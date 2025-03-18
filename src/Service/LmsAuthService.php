<?php

namespace Athenea\LMS\Service;

use Athenea\LMS\Document\User;
use DateTimeImmutable;
use Doctrine\ODM\MongoDB\DocumentManager;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Psr\Log\LoggerInterface;

class LmsAuthService {

    public function __construct(
        private string $lmsPublicKey,
        private string $appPrivateKey,
        private string $appPublicKey,
        private string $devPrivateKeyPath,
        private string $devPublicKeyPath,
        private bool $verifyLmsSignature,
        private DocumentManager $dm,
        private ?LoggerInterface $logger = null,
    ){

    }

    public function createSignature(?string $cip = null, ?string $dni = null, bool $devEnv = false){
        $privateKeyPem = $devEnv ? file_get_contents($this->devPrivateKeyPath) : $this->appPrivateKey;
        $keyIdentifier = $devEnv ? 'dev_private_key' : 'app_private_key';
        if(is_null($cip)) $cip = "";
        if(is_null($dni)) $dni = "";
        $payload = "CIP=$cip&DNI=$dni";

        $this->logger?->debug('Creating signature', [
            'key_type' => $keyIdentifier,
            'payload' => $payload
        ]);

        $privateKey = openssl_pkey_get_private($privateKeyPem);
        if ($privateKey === false) {
            $this->logger?->error('Failed to load private key', ['key_type' => $keyIdentifier]);
            throw new Exception('Failed to load private key');
        }
        $hash = hash('sha256', $payload, true);
        openssl_sign($hash, $signature, $privateKey, OPENSSL_ALGO_SHA256);

        $this->logger?->debug('Signature created successfully', [
            'key_type' => $keyIdentifier,
            'signature' => $signature
        ]);

        return base64_encode($signature);
    }

    public function verifyLmsSignature(string $signature, ?string $cip = null, ?string $dni = null, bool $devEnv = false){
        if(!$this->verifyLmsSignature) {
            $this->logger?->debug('LMS signature verification skipped (disabled in config)');
            return;
        }

        $publicKeyPem = $devEnv ? file_get_contents($this->devPublicKeyPath) : $this->lmsPublicKey;
        $keyIdentifier = $devEnv ? 'dev_public_key' : 'lms_public_key';

        $this->logger?->debug('Verifying LMS signature', [
            'key_type' => $keyIdentifier,
            'public_key' => $publicKeyPem,
            'signature' => $signature
        ]);
        $this->verifySignature($publicKeyPem, $signature, $cip, $dni);
    }

    public function verifyAppSignature(string $signature, ?string $cip = null, ?string $dni = null){
        $this->logger?->debug('Verifying APP signature', [
            'key_type' => 'app_public_key',
            'public_key' => $this->appPublicKey,
            'signature' => $signature
        ]);

        $this->verifySignature($this->appPublicKey, $signature, $cip, $dni);
    }

    public function verifySignature(string $key, string $signature, ?string $cip = null, ?string $dni = null){
        $publicKey = openssl_pkey_get_public($key);
        if ($publicKey === false) {
            $this->logger?->error('Failed to load public key', [
                'public_key' => $key
            ]);
            throw new Exception('Failed to load public key');
        }

        if(is_null($cip)) $cip = "";
        if(is_null($dni)) $dni = "";
        $payload = "CIP=$cip&DNI=$dni";

        $signature = base64_decode($signature);
        $hash = hash('sha256', $payload, true);

        $verified = openssl_verify($hash, $signature, $publicKey, OPENSSL_ALGO_SHA256);

        $this->logger?->debug('Signature verification attempt', [
            'payload' => $payload,
            'public_key' => $key,
            'verified' => $verified === 1
        ]);

        if ($verified === 1) {
            $this->logger?->debug('Signature verified successfully');
        } else {
            $this->logger?->error('Invalid signature', [
                'payload' => $payload,
                'public_key' => $key
            ]);
            throw new InvalidSignature("Signatura no vàlida");
        }
    }

    public function tokenToUser(string $token): ?User
    {
        [$id, $uniqueCode] = explode(":", $token);
        $this->logger?->debug('Looking up user by token', [
            'user_id' => $id,
            'unique_code_length' => strlen($uniqueCode)
        ]);

        return $this->dm->getRepository(User::class)
            ->createQueryBuilder()
            ->field('id')->equals($id)
            ->field('uniqueCode')->equals($uniqueCode)
            ->field('used')->equals(false)
            ->field('expiresAt')->gt(new DateTimeImmutable())
            ->getQuery()
            ->getSingleResult();
    }

    public function extractToken(Request $request){
        $content = $request->getContent();
        $token = json_decode($content)->token;
        $this->logger?->debug('Token extracted from request', [
            'token_length' => strlen($token)
        ]);
        return $token;
    }

    public function useToken(User $user){
        $this->logger?->debug('Marking token as used', [
            'user_id' => $user->getId()
        ]);

        $user->setUsed(true);
        $this->dm->persist($user);
        $this->dm->flush();
    }
}

class InvalidSignature extends Exception
{

}
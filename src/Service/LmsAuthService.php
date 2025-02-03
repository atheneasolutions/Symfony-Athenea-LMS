<?php

namespace Athenea\LMS\Service;

use Athenea\LMS\Document\User;
use DateTimeImmutable;
use Doctrine\ODM\MongoDB\DocumentManager;
use Exception;
use Symfony\Component\HttpFoundation\Request;

class LmsAuthService {


    public function __construct(
        private string $lmsPublicKey,
        private string $appPrivateKey,
        private string $appPublicKey,
        private string $devPrivateKeyPath,
        private string $devPublicKeyPath,
        private bool $verifyLmsSignature,
        private DocumentManager $dm,
    ){

    }

    public function createSignature(?string $cip = null, ?string $dni = null, bool $devEnv = false){
        // Load your RSA private key (PEM format)
        $privateKeyPem = $devEnv ? file_get_contents($this->devPrivateKeyPath) : $this->appPrivateKey;

        // Load RSA private key from PEM
        $privateKey = openssl_pkey_get_private($privateKeyPem);

        // Payload (message) to be signed
        if(is_null($cip)) $cip = "";
        if(is_null($dni)) $dni = "";
        $payload = "CIP=$cip&DNI=$dni";

        // Step 1: Hash the payload with SHA-256
        $hash = hash('sha256', $payload, true);

        // Step 2: Sign the hash with the RSA private key
        openssl_sign($hash, $signature, $privateKey, OPENSSL_ALGO_SHA256);

        return base64_encode($signature);
    }

    public function verifyLmsSignature(string $signature, ?string $cip = null, ?string $dni = null, bool $devEnv = false){
        // Load your RSA public key (PEM format)
        if(!$this->verifyLmsSignature) return;
        $publicKeyPem = $devEnv ? file_get_contents($this->devPublicKeyPath) : $this->lmsPublicKey;
        $this->verifySignature($publicKeyPem, $signature, $cip, $dni);
    }

    public function verifyAppSignature(string $signature, ?string $cip = null, ?string $dni = null){
        $this->verifySignature($this->appPublicKey, $signature, $cip, $dni);
    }

    public function verifySignature(string $key, string $signature, ?string $cip = null, ?string $dni = null){
        // Load RSA public key from PEM
        $publicKey = openssl_pkey_get_public($key);

        // Payload (message)
        if(is_null($cip)) $cip = "";
        if(is_null($dni)) $dni = "";
        $payload = "CIP=$cip&DNI=$dni";

        // The signature to verify (Base64 encoded)
        $signature = base64_decode($signature);  // Provide the signature here

        // Step 1: Hash the payload with SHA-256
        $hash = hash('sha256', $payload, true);

        // Step 2: Verify the signature using the RSA public key
        $verified = openssl_verify($hash, $signature, $publicKey, OPENSSL_ALGO_SHA256);

        if ($verified === 1) {
        } else throw new InvalidSignature("Signatura no vàlida");
    }

    public function tokenToUser(string $token): ?User
    {
        [$id, $uniqueCode] = explode(":", $token);
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
        return $token;
    }

    public function useToken(User $user){
        $user->setUsed(true);
        $this->dm->persist($user);
        $this->dm->flush();
    }
}


class InvalidSignature extends Exception
{

}
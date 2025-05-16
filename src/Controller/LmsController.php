<?php

namespace Athenea\LMS\Controller;

use Athenea\LMS\Document\User;
use Athenea\LMS\Service\LmsAuthService;
use DateTimeImmutable;
use Doctrine\ODM\MongoDB\DocumentManager;
use Exception;
use ProxyManager\Signature\Exception\InvalidSignatureException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class LmsController extends AbstractController{



    #[Route("/lms/form", name: "athenea_lms_form", methods: ['POST'])]
    public function autoForm(
        Request $request,
        LmsAuthService $lmsAuthService
    )
    {
        // Receive the form parameters from the POST request
        $cip = $request->request->get('CIP');
        $dni = $request->request->get('DNI');
        $dn = $request->request->get('DN');
        $firma = $request->request->get('FIRMA');
        $idioma = $request->request->get('IDIOMA');
        $codiUserConnectat = $request->request->get('CODI_USER_CONNECTAT');
        $nomUserConnectat = $request->request->get('NOM_USER_CONNECTAT');
        $dadesAuxiliars = $request->request->get('DADES_AUXILIARS');
        $appEnv = $request->request->get('ENV');

        try {
            $lmsAuthService->verifyLmsSignature($firma, $cip, $dni, $appEnv == 'dev');
        }
        catch(InvalidSignatureException $e){
            throw $this->createAccessDeniedException();
        }
        
        $appFirma = $lmsAuthService->createSignature($cip, $dni);

        $url = $this->getParameter('athenea_lms.form_url');
        if(!$url){
            $url = $this->generateUrl(route: "athenea_lms_form_lms", referenceType: UrlGeneratorInterface::ABSOLUTE_URL);
            $url = str_replace('http://', 'https://', $url);
        }
        return $this->render('@AtheneaLaMevaSalut/auto_form.html.twig', [
            'url' => $url,
            'CIP' => $cip,
            'DNI' => $dni,
            'DN' => $dn,
            'firma' => $appFirma,
            'codiUserConnectat' => $codiUserConnectat,
            'nomUserConnectat' => $nomUserConnectat,
            'dadesAuxiliars' => $dadesAuxiliars,
            'idioma' => $idioma
        ]);
    }


    #[Route("/lms/form_lms", name: "athenea_lms_form_lms", methods: ['POST'])]
    public function lmsForm(
        LmsAuthService $lmsAuthService,
        DocumentManager $dm,
        Request $request
    )
    {
        // Receive the form parameters from the POST request
        $cip = $request->request->get('CIP');
        $dni = $request->request->get('DNI');
        $dn = $request->request->get('DN');
        $firma = $request->request->get('FIRMA');
        $codiUserConnectat = $request->request->get('CODI_USER_CONNECTAT');
        $nomUserConnectat = $request->request->get('NOM_USER_CONNECTAT');
        $dadesAuxiliars = $request->request->get('DADES_AUXILIARS');
        $idioma = $request->request->get('IDIOMA');

        try{
            $lmsAuthService->verifyAppSignature($firma, $cip, $dni);
        } catch(InvalidSignatureException $e){
            throw $this->createAccessDeniedException();
        }
        $uniqueCode =bin2hex(random_bytes(16));

        $user = new User(
            cip: $cip,
            uniqueCode: $uniqueCode,
            codiUserConnectat: $codiUserConnectat,
            nomUserConnectat: $nomUserConnectat,
            dni: $dni,
            dn: $dn,
            dadesAuxiliars: $dadesAuxiliars,
            idioma: $idioma,
            expiresAt: new DateTimeImmutable('now + 8 hours')
        );
        $dm->persist($user);
        $dm->flush();

        $url = $this->getParameter('athenea_lms.app_url');
        $token = $user->getId() . ":" . $user->getUniqueCode();

        $parsedUrl = parse_url($url);

        // Check if the URL already has query parameters
        if (isset($parsedUrl['query'])) {
            // If query parameters exist, append the new parameter using '&'
            $url .= '&' .  'token=' . urlencode($token);
        } else {
            // If no query parameters exist, append using '?'
            $url .= '?' .  'token=' . urlencode($token);
        }
        return $this->redirect($url);
    }


    #[Route("/lms/user", name: "athenea_lms_get_user", methods: ['GET'])]
    public function lmsUser(
        LmsAuthService $lmsAuthService,
        Request $request
    ){
        $token = $lmsAuthService->extractToken($request);
        return $this->json($lmsAuthService->tokenToUser($token));
    }
}
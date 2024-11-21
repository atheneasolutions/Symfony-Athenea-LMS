<?php

namespace Athenea\LMS\Controller;

use Athenea\LMS\Service\LmsAuthService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class DevEnvController extends AbstractController{



    #[Route("/lms/env-dev/home", name: "athenea_lms_env_dev_home")]
    public function home(SessionInterface $session)
    {
        // Retrieve app parameters from configuration
        $appUrl = $this->getParameter('athenea_lms.app_url');
        $appLogo = $this->getParameter('athenea_lms.app_logo');
        $appName = $this->getParameter('athenea_lms.app_name');

        // Retrieve user identification data from session if available
        $userData = $session->get('user_data', [
            'IDIOMA' => 'ca'
        ]);

        return $this->render('@AtheneaLaMevaSalut/env_dev_home.html.twig', [
            'url' => $appUrl,
            'logo' => $appLogo,
            'name' => $appName,
            'userData' => $userData, // Pass the stored user data to the template
        ]);
    }

    #[Route("/lms/env-dev/enter", name: "athenea_lms_enter", methods: ['POST'])]
    public function identify(
        Request $request,
        SessionInterface $session,
        LmsAuthService $lmsAuthService
    )
    {
        // Collect form data
        $userData = [
            'CIP' => $request->get('CIP'),
            'DNI' => $request->get('DNI'),
            'IDIOMA' => $request->get('IDIOMA'),
            'DN' => $request->get('DN'),
            'CODI_USER_CONNECTAT' => $request->get('CODI_USER_CONNECTAT'),
            'NOM_USER_CONNECTAT' => $request->get('NOM_USER_CONNECTAT'),
            'DADES_AUXILIARS' => $request->get('DADES_AUXILIARS'),
        ];

        // Store the data in session for future use
        $session->set('user_data', $userData);

        // User data calculate signature
        $userData['FIRMA'] = $lmsAuthService->createSignature($request->get('CIP'), $request->get('DNI'), devEnv: true);
        $userData['ENV'] = 'dev';

        // Get Backend form
        $formUrl = $this->generateUrl('athenea_lms_form', referenceType: UrlGeneratorInterface::ABSOLUTE_URL);
        $httpClient = HttpClient::create();
        $content = $httpClient->request(method: "POST", url: $formUrl, options: ['verify_peer' => false, 'body' => $userData] )->getContent();
        
        // Redirect back to the home page or to the app URL
        return $this->render('@AtheneaLaMevaSalut/env_dev_app.html.twig', ['form' => $content]);
    }
}
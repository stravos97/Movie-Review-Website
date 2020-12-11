<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="website_login")
     */
    public function login(AuthenticationUtils $authenticationUtils, Security $security): Response
    {
        /**
         * Logged in users can't access the login page
         */

                if ($security->isGranted('ROLE_USER')){
            throw $this->createAccessDeniedException('No Access');
        }

        $error = $authenticationUtils->getLastAuthenticationError();

        $lastUsername = $authenticationUtils->getLastUsername();
        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,

        ]);
    }

    /**
     * @Route("/logout", name="website_logout")
     */
    public function logout(){
        dd('hello');
    }

}

<?php

namespace App\Controller\UserManagement;

use App\Security\Exception\MaxLoginAttemptException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Security;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils, Security $security): Response
    {
        if ($security->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('admin_published_document_actif');
        }  

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError(); //MaxLoginAttemptException

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        if ($error instanceof MaxLoginAttemptException) {
            $maxLoginAttempt = true;
        } else $maxLoginAttempt = false;

        return $this->render('UserManagement/Security/login.html.twig', [
            'last_username' => $lastUsername, 
            'error' => $error,
            'maxLoginAttempt' => $maxLoginAttempt
        ]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}

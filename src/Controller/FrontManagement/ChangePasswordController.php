<?php

namespace App\Controller\FrontManagement;

use App\Form\UserManagement\PasswordType;
use App\Repository\UserManagement\ResettingRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ChangePasswordController extends AbstractController
{
    /**
     * @Route("/change_password", methods={"GET|POST"}, name="change_password")
     */
    public function changePassword(ResettingRepository $repository, Request $request): Response
    {
        if (!$this->getUser()) {
            return new RedirectResponse($this->generateUrl('security_login'));
        }

        $form = $this->createForm(PasswordType::class, []);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getUser()->setChangePassword(false);
            $repository->setPassword($this->getUser(), $form->getNormData()['password']);
            $this->addFlash('success', 'message.password_has_been_reset');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('UserManagement/Resetting/password_change.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}

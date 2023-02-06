<?php

namespace App\Controller\FrontManagement;

use App\Entity\UserManagement\User;
use App\Form\UserManagement\UserType;
use App\Form\UserManagement\PasswordType;
use App\Repository\UserManagement\ResettingRepository;
use App\Service\UserManagement\UserService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserProfileController extends AbstractController
{
    /**
     * Displays a form to edit an existing User entity.
     *
     * @Route("/user/profile/{username}",methods={"GET", "POST"}, name="user_profile")
     *
     * @Security("is_granted('ROLE_USER')")
     */
    public function edit(Request $request, User $user, UserService $service, TranslatorInterface $translator): Response
    {
        if ($user !== $this->getUser()) {
            throw $this->createAccessDeniedException($translator->trans('message.denied'));
        }

        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $service->update($user);
        }

        return $this->render('FrontManagement/User/profile.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/user/change_password/{username}", methods={"GET|POST"}, name="user_change_password")
     */
    public function changePassword(Request $request, User $user, ResettingRepository $repository, TranslatorInterface $translator): Response
    {
        if ($user !== $this->getUser()) {
            throw $this->createAccessDeniedException($translator->trans('message.denied'));
        }

        $form = $this->createForm(PasswordType::class, []);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getUser()->setChangePassword(false);
            $repository->setPassword($this->getUser(), $form->getNormData()['password']);
            $this->addFlash('success', 'message.password_has_been_reset');

            return $this->redirectToRoute('app_logout');
        }

        return $this->render('FrontManagement/User/password_change.html.twig', [
            'form' => $form->createView(),
        ]);
    }    
}

<?php

namespace App\MessageHandler\UserManagement;

use App\Entity\UserManagement\User;
use App\Mailer\Mailer;
use App\Message\UserManagement\SendUserCreated;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class SendUserCreatedHandler implements MessageHandlerInterface
{
    /**
     * @var Mailer
     */
    private $mailer;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var UrlGeneratorInterface
     */
    private $router;

    /**
     * @var Environment
     */
    private $twig;
    private $parameter;

    public function __construct(Environment $twig, Mailer $mailer, TranslatorInterface $translator, ParameterBagInterface $parameter, UrlGeneratorInterface $router)
    {
        $this->mailer = $mailer;
        $this->translator = $translator;
        $this->twig = $twig;
        $this->router = $router;
        $this->parameter = $parameter;
    }

    public function __invoke(SendUserCreated $sendUserCreated)
    {
        $this->mailer->sendMail($this->getSender(), $sendUserCreated->getUser()->getEmail(), $this->getSubject(), $this->getBody($sendUserCreated->getUser(), $sendUserCreated->getPassword()));
    }

    private function getSender(): array
    {
        return [$this->parameter->get('admin_email')];
    }

    private function getSubject(): string
    {
        return $this->translator->trans('email.new_user');
    }

    private function getBody(User $user, string $password): ?string
    {
        return $this->twig->render('UserManagement/emails/user_created.txt.twig', [
            'user' => $user,
            'password' => $password
        ]);
    }
}

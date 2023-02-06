<?php

namespace App\MessageHandler\UserManagement;

use App\Entity\UserManagement\User;
use App\Mailer\Mailer;
use App\Message\UserManagement\SendResetPasswordLink;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class SendResetPasswordLinkHandler implements MessageHandlerInterface
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

    /**
    * @var ParameterBagInterface
    */
    private $parameter;

    public function __construct(Environment $twig, Mailer $mailer, TranslatorInterface $translator, UrlGeneratorInterface $router, ParameterBagInterface $parameter)
    {
        $this->mailer = $mailer;
        $this->translator = $translator;
        $this->router = $router;
        $this->twig = $twig;
        $this->parameter = $parameter;
    }

    public function __invoke(SendResetPasswordLink $sendResetPasswordLink)
    {
        /** @var User $user */
        $user = $sendResetPasswordLink->getUser();

        $body = $this->getBody($user);

        $this->mailer->sendMail($this->getSender(), $user->getEmail(), $this->getSubject(), $body);
    }

    private function getSender(): array
    {
        return [$this->parameter->get('admin_email')];
        // $host = $this->router->getContext()->getHost();

        // return ['no-reply@' . $host => $host];
    }

    private function getSubject(): string
    {
        return $this->translator->trans('resetting.email.subject');
    }

    private function getConfirmationUrl(User $user): string
    {
        return $this->router->generate(
            'password_reset_confirm',
            ['token' => $user->getConfirmationToken()],
            0
        );
    }

    private function getBody(User $user): ?string
    {
        return $this->twig->render('UserManagement/emails/reset.txt.twig', [
                  'confirmationUrl' => $this->getConfirmationUrl($user),
                  'username' => $user->getUsername(),
                ]);
    }
}

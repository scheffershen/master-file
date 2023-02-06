<?php

namespace App\MessageHandler\UserManagement;

use App\Mailer\Mailer;
use App\Message\UserManagement\SendUserImportLog;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class SendUserImportLogHandler implements MessageHandlerInterface
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

    public function __construct(Environment $twig, Mailer $mailer, TranslatorInterface $translator, UrlGeneratorInterface $router, ParameterBagInterface $parameter)
    {
        $this->mailer = $mailer;
        $this->translator = $translator;
        $this->router = $router;
        $this->twig = $twig;
        $this->parameter = $parameter;
    }

    public function __invoke(SendUserImportLog $sendUserImportLog)
    {
        $this->mailer->sendMail($this->getSender(), $this->parameter->get('admin_email'), $this->getSubject() . ' ' . date('d/m/Y'), $this->getBody($sendUserImportLog->getLog()));
    }

    private function getSender(): array
    {
        return [$this->parameter->get('admin_email')];
        // $host = $this->router->getContext()->getHost();

        // return ['no-reply@' . $host => $host];
    }

    private function getSubject(): string
    {
        return $this->translator->trans('email.new_log');
    }

    private function getBody(array $log): ?string
    {
        return $this->twig->render('UserManagement/emails/user_import_log.txt.twig', [
                  'log' => $log,
                ]);
    }
}

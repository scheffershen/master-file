<?php

namespace App\Mailer;

use App\Entity\PSMFManagement\PSMFHistory;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class PSMFHistoryMailing
{
    private $mailer;
    private $translator;
    private $router;
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

    public function mailing(PSMFHistory $pSMFHistory)
    {
        $this->mailer->sendMail($this->getSender(), $this->parameter->get('admin_email'), $this->getSubject(), $this->getBody($pSMFHistory));
    }

    private function getSender(): array
    {
        // get all no PV User 
        return [$this->parameter->get('admin_email')];
    }

    private function getSubject(): string
    {
        return $this->translator->trans('psmfHistory.created');
    }

    private function getBody(PSMFHistory $pSMFHistory): ?string
    {
        // dd($pSMFHistory); exit();
        return $this->twig->render('PSMFManagement/PSMFHistory/emails/psmfhistory.html.twig', [
            'pSMFHistory' => $pSMFHistory,
        ]);
    }
}

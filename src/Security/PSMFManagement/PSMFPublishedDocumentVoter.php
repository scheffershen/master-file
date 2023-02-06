<?php

namespace App\Security\PSMFManagement;

use App\Entity\PSMFManagement\PSMF;
use App\Manager\PSMFManagement\PSMFDocumentManager;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class PSMFPublishedDocumentVoter extends Voter
{
    private Security $security;
    private PSMFDocumentManager $pSMFDocumentManager;
    private SessionInterface $session;
    private TranslatorInterface $translator;
    private EventDispatcherInterface $dispatcher;
    private $url = null;
    private UrlGeneratorInterface $router;

    public function __construct(Security $security, PSMFDocumentManager $pSMFDocumentManager,SessionInterface $session, TranslatorInterface $translator, UrlGeneratorInterface $router, EventDispatcherInterface $dispatcher)
    {
        $this->security = $security;
        $this->pSMFDocumentManager = $pSMFDocumentManager;
        $this->session = $session;
        $this->translator = $translator;
        $this->dispatcher = $dispatcher;
        $this->router = $router;
    }

    protected function supports($attribute, $subject)
    {
        return \in_array($attribute, ['PSMF_PUBLISH'], true)
            && $subject instanceof PSMF;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token)
    {
        switch ($attribute) {
            case 'PSMF_PUBLISH':
                return $this->canPublish($subject, $token);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canPublish(PSMF $psmf, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        } elseif ($this->security->isGranted('ROLE_UTILISATEUR')) {
            if ($this->pSMFDocumentManager->correspondanceLocaleMissing($psmf) > 0 ) {
                //throw new \Exception($this->translator->trans('psmf.correspondanceLocaleMissing'));
                $this->session->getFlashBag()->add('error', $this->translator->trans('psmf.correspondanceLocaleMissing'));
                $this->url = $this->router->generate('admin_psmf_correspondance_locale3_edit', ['psmf'=> $psmf->getId()]);                
                $this->dispatcher->addListener(KernelEvents::RESPONSE, [$this, 'onKernelResponse']); 
                return false;
            }
            // correspondanceGlobaleMissing
            if ($this->pSMFDocumentManager->correspondanceGlobaleMissing() > 0 ) {            
                $this->session->getFlashBag()->add('error', $this->translator->trans('psmf.correspondanceGlobaleMissing'));
                $this->url = $this->router->generate('admin_correspondance_globale3');                
                $this->dispatcher->addListener(KernelEvents::RESPONSE, [$this, 'onKernelResponse']); 
                return false;
            }             
            return true;
        }

        return false;
    }

    public function onKernelResponse(ResponseEvent $event)
    {
        $response = new RedirectResponse($this->url);
        $event->setResponse($response);
    }    
}

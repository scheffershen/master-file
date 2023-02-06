<?php

namespace App\Security\TemplateManagement;

use App\Entity\TemplateManagement\Classe;
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

class ClasseVoter extends Voter
{
    private Security $security;
    private SessionInterface $session;
    private TranslatorInterface $translator;
    private EventDispatcherInterface $dispatcher;
    private $url = null;
    private UrlGeneratorInterface $router;

    public function __construct(Security $security, SessionInterface $session, TranslatorInterface $translator, UrlGeneratorInterface $router, EventDispatcherInterface $dispatcher)
    {
        $this->security = $security;
        $this->session = $session;
        $this->translator = $translator;
        $this->dispatcher = $dispatcher;
        $this->router = $router;
    }

    protected function supports($attribute, $subject)
    {
        return \in_array($attribute, ['CLASSE_DISABLE', 'CLASSE_DELETE'], true)
            && $subject instanceof Classe;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token)
    {
        switch ($attribute) {
            case 'CLASSE_DISABLE':
                return $this->canDisable($subject, $token);
            case 'CLASSE_DELETE':
                return $this->canDisable($subject, $token);                
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canDisable(Classe $classe, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        } elseif ($this->security->isGranted('ROLE_ADMIN')) {
            if ($classe->getCorrespondances() > 0 || count($classe->getPSMFs()) > 0 || count($classe->getSections()) > 0 || count($classe->getVariables()) > 0) {
                $this->session->getFlashBag()->add('error', $this->translator->trans('classe.in_used'));
                $this->url = $this->router->generate('admin_classe_show', ['id'=> $classe->getId()]);
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

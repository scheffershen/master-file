<?php

namespace App\Security\TemplateManagement;

use App\Entity\TemplateManagement\Variable;
use App\Entity\LovManagement\Obligation;
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

class VariableVoter extends Voter
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
        return \in_array($attribute, ['VARIABLE_DISABLE', 'VARIABLE_DELETE'], true)
            && $subject instanceof Variable;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token)
    {
        switch ($attribute) {
            case 'VARIABLE_DISABLE':
                return $this->canDisbale($subject, $token);
            case 'VARIABLE_DELETE':
                return $this->canDisbale($subject, $token);                
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canDisbale(Variable $variable, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        } elseif ($this->security->isGranted('ROLE_ADMIN')) {
            // FM 10
            //if (!$variable->getCorrespondances()->isEmpty() || count($variable->getPSMFs()) > 0) {
            if ($variable->getObligation()->getCode() == Obligation::OBLIGATOIRE) {
                $this->session->getFlashBag()->add('error', $this->translator->trans('variable.in_used'));
                $this->url = $this->router->generate('admin_variable_show', ['id'=> $variable->getId()]);
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

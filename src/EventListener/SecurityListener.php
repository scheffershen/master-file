<?php

namespace App\EventListener;

use App\Entity\UserManagement\FailedLoginAttempt;
use App\Entity\UserManagement\User;
use App\Entity\UserManagement\Tracking;
use App\Repository\UserManagement\FailedLoginAttemptRepository;
use App\Message\TeamsMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\AuthenticationEvents;
use Symfony\Component\Security\Core\Event\AuthenticationFailureEvent;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

class SecurityListener implements EventSubscriberInterface
{
    private $router;
    private $dispatcher;
    private $entityManager;
    private $messageBus;
    private $authenticationUtils;
    private $requestStack;
    private $failedLoginAttemptRepository;

    public function __construct(UrlGeneratorInterface $router, EventDispatcherInterface $dispatcher, MessageBusInterface $messageBus, AuthenticationUtils $authenticationUtils, RequestStack $requestStack, EntityManagerInterface $entityManager, FailedLoginAttemptRepository $failedLoginAttemptRepository)
    {
        $this->router = $router;
        $this->dispatcher = $dispatcher;
        $this->entityManager = $entityManager;
        $this->messageBus = $messageBus;
        $this->authenticationUtils = $authenticationUtils;
        $this->requestStack = $requestStack;
        $this->failedLoginAttemptRepository = $failedLoginAttemptRepository;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            SecurityEvents::INTERACTIVE_LOGIN => 'onLogin',
            AuthenticationEvents::AUTHENTICATION_FAILURE => 'onLoginFailure'
        ];
    }

    public function onLogin(InteractiveLoginEvent $event)
    {
        $user = $event->getAuthenticationToken()->getUser();

        $request = $event->getRequest();
        $route = $request->get('_route');

        $userDetails = [
            'id' =>             $user->getId(),
            'username' =>       $user->getUsername(),
            'email' =>          $user->getEmail(),
            'authenticated' =>  true
        ];

        $entity = new Tracking();
        $entity->setController('App\Controller\UserManagement\SecurityController');
        $entity->setAction('loginAction');
        $entity->setQueryRequest(json_encode($userDetails));
        $entity->setPathInfo('/en/login');
        $entity->setHttpMethod($request->getMethod());
        $entity->setIpRequest($request->getClientIp());
        $entity->setLang($request->getLocale());
        $entity->setUriRequest($request->getUri());
        $entity->setCreated((new \DateTime('now')));
        $entity->setUser($user);
        try {
            $this->entityManager->persist($entity);
            $this->entityManager->flush($entity);
        } catch (\Throwable $exception) {
            throw new \Exception($exception->getMessage());
        }

        $this->messageBus->dispatch(new TeamsMessage('', 'Login', $user->getFirstName().' '.$user->getLastName()));

        if ($route != 'change_password' && $route != null) {
            if ($user->getChangePassword()) {
                $this->dispatcher->addListener(KernelEvents::RESPONSE, [
                            $this,
                            'onKernelResponse'
                ]);
            }
        }
    }

    public function onKernelResponse(ResponseEvent $event)
    {
        $response = new RedirectResponse($this->router->generate('change_password'));
        $event->setResponse($response);
    }

    public function onLoginFailure(AuthenticationFailureEvent $event)
    {
        $username = $this->authenticationUtils->getLastUsername();
        $request = $this->requestStack->getCurrentRequest();

        $this->failedLoginAttemptRepository->save(FailedLoginAttempt::createFromRequest($request));

        $existingUser = $this->entityManager->getRepository(User::class)->loadUserByUsername($username);

        if ($existingUser) {
            $userDetails = [
                'id' =>             $existingUser->getId(),
                'username' =>       $existingUser->getUsername(),
                'email' =>          $existingUser->getEmail(),
                'authenticated' =>  false
            ];
        } else {
            $userDetails = [
                'username' =>           $username,
                'authenticated' =>  false
            ];
        }

        $entity = new Tracking();
        $entity->setController('App\Controller\UserManagement\SecurityController');
        $entity->setAction('loginFailureAction');
        $entity->setQueryRequest(json_encode($userDetails));
        $entity->setPathInfo('/'.$request->getLocale().'/login');
        $entity->setHttpMethod($request->getMethod());
        $entity->setIpRequest($request->getClientIp());
        $entity->setLang($request->getLocale());
        $entity->setUriRequest($request->getUri());
        $entity->setCreated((new \DateTime('now')));
        try {
            $this->entityManager->persist($entity);
            $this->entityManager->flush($entity);
        } catch (\Throwable $exception) {
            throw new \Exception($exception->getMessage());
        }
    }    
}
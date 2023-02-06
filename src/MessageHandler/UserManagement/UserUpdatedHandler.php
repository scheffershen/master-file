<?php

namespace App\MessageHandler\UserManagement;

use App\Entity\PSMFManagement\PSMFHistory; 
use App\Mailer\PSMFHistoryMailing;
use App\Message\UserManagement\UserUpdated;
use App\Repository\PSMFManagement\PSMFRepository;
use App\Serializer\UserManagement\UserSerializer;
use App\Utils\DiffsInterface;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;
use Psr\Log\LoggerInterface;

class UserUpdatedHandler implements MessageHandlerInterface
{
	private $requestStack;
    private $security;
    private $session;
    private $translator;
    private $entityManager;
    private $userSerializer;
    private $logger;
    private $diffs;
    private $pSMFRepository;
    private $pSMFHistoryMailing;

    public function __construct(RequestStack $requestStack, EntityManagerInterface $entityManager, Security $security, UserSerializer $userSerializer, SessionInterface $session, PSMFRepository $pSMFRepository, LoggerInterface $logger, TranslatorInterface $translator, DiffsInterface $diffs, PSMFHistoryMailing $pSMFHistoryMailing)
    {
    	$this->requestStack = $requestStack;
    	$this->security = $security;
    	$this->session = $session;
    	$this->logger = $logger;
    	$this->diffs = $diffs;
    	$this->translator = $translator;
        $this->entityManager = $entityManager;
        $this->pSMFRepository = $pSMFRepository;
        $this->userSerializer = $userSerializer;
        $this->pSMFHistoryMailing = $pSMFHistoryMailing;
    }

    public function __invoke(UserUpdated $userUpdated)
    {
        $user = $userUpdated->getUser();
        $action = $userUpdated->getAction();
        
        try {
        	$pSMFHistory = new PSMFHistory();
            $psmfs = $this->pSMFRepository->findByUser($user);
            foreach ($psmfs as $psmf) {
                $pSMFHistory->addPsmf($psmf);
            }             
	        $pSMFHistory->setPvuser($user);
	        $pSMFHistory->setReason($user->getReason());
            $pSMFHistory->setAction($action);
	        $pSMFHistory->setCreateUser($this->security->getUser());
	        $pSMFHistory->setUpdateUser($this->security->getUser()); 
	        $pSMFHistory->setIp($this->requestStack->getCurrentRequest()->getClientIp());
	        
            $diff = json_encode($this->diffs->diffs($userUpdated->getOld(), $this->userSerializer->serialize($user)));
            
            // Condition to check array diff is empty or not
            if(!empty($diff)) {
                $pSMFHistory->setDiffs($diff); 
                             
                $this->entityManager->persist($pSMFHistory);
                $this->entityManager->flush();
            }
            //$this->pSMFHistoryMailing->mailing($pSMFHistory);

        } catch (DBALException $exception) {
            $this->logger->error($exception->getMessage());
            $this->session->getFlashBag()->add('error', $exception->getMessage());
        } catch (\Throwable $exception) {
        	$this->logger->error($exception->getMessage());
            $this->session->getFlashBag()->add('error', $exception->getMessage());
        } 
    }	

}
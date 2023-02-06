<?php

namespace App\MessageHandler\TemplateManagement;

use App\Entity\PSMFManagement\PSMFHistory; 
use App\Mailer\PSMFHistoryMailing;
use App\Message\TemplateManagement\CorrespondanceLocaleHistoryUpdated;
use App\Serializer\TemplateManagement\CorrespondanceLocaleHistorySerializer;
use App\Utils\DiffsInterface;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;
use Psr\Log\LoggerInterface;

class CorrespondanceLocaleHistoryUpdatedHandler implements MessageHandlerInterface
{
	private $requestStack;
    private $security;
    private $session;
    private $translator;
    private $entityManager;
    private $correspondanceLocaleHistorySerializer;
    private $logger;
    private $diffs;
    private $pSMFHistoryMailing;

    public function __construct(RequestStack $requestStack, EntityManagerInterface $entityManager, Security $security, CorrespondanceLocaleHistorySerializer $correspondanceLocaleHistorySerializer, SessionInterface $session, LoggerInterface $logger, TranslatorInterface $translator, DiffsInterface $diffs, PSMFHistoryMailing $pSMFHistoryMailing)
    {
    	$this->requestStack = $requestStack;
    	$this->security = $security;
    	$this->session = $session;
    	$this->logger = $logger;
    	$this->diffs = $diffs;
    	$this->translator = $translator;
        $this->entityManager = $entityManager;
        $this->correspondanceLocaleHistorySerializer = $correspondanceLocaleHistorySerializer;
        $this->pSMFHistoryMailing = $pSMFHistoryMailing;
    }

    public function __invoke(CorrespondanceLocaleHistoryUpdated $correspondanceLocaleHistoryUpdated)
    {
        $correspondanceLocaleHistory = $correspondanceLocaleHistoryUpdated->getCorrespondanceLocaleHistory();
        $action = $correspondanceLocaleHistoryUpdated->getAction();
        
        try {
            $diffs = $this->diffs->diffsCorrespondance($correspondanceLocaleHistoryUpdated->getOld(), $this->correspondanceLocaleHistorySerializer->serialize($correspondanceLocaleHistory));

            if ($diffs) {
                $pSMFHistory = new PSMFHistory();
                $pSMFHistory->addPsmf($correspondanceLocaleHistory->getPsmf());
                $pSMFHistory->setCorrespondanceLocale($correspondanceLocaleHistory);
                $pSMFHistory->setReason($correspondanceLocaleHistory->getReason());
                $pSMFHistory->setAction($action);
                $pSMFHistory->setCreateUser($this->security->getUser());
                $pSMFHistory->setUpdateUser($this->security->getUser()); 
                $pSMFHistory->setIp($this->requestStack->getCurrentRequest()->getClientIp());

                $diff = json_encode($this->diffs->diffsCorrespondance($correspondanceLocaleHistoryUpdated->getOld(), $this->correspondanceLocaleHistorySerializer->serialize($correspondanceLocaleHistory)));
                
                // Condition to check array diff is empty or not
                if(!empty($diff)) {
                    $pSMFHistory->setDiffs($diff); 
                    
                    $this->entityManager->persist($pSMFHistory);
                    $this->entityManager->flush();
                }
                
                //$this->pSMFHistoryMailing->mailing($pSMFHistory);
            }
        } catch (DBALException $exception) {
            $this->logger->error($exception->getMessage());
            $this->session->getFlashBag()->add('error', $exception->getMessage());
        } catch (\Throwable $exception) {
        	$this->logger->error($exception->getMessage());
            $this->session->getFlashBag()->add('error', $exception->getMessage());
        } 
    }	

}
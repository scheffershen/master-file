<?php

namespace App\MessageHandler\TemplateManagement;

use App\Entity\PSMFManagement\PSMFHistory; 
use App\Mailer\PSMFHistoryMailing;
use App\Message\TemplateManagement\CorrespondanceGlobaleHistoryUpdated;
use App\Repository\PSMFManagement\PSMFRepository;
use App\Serializer\TemplateManagement\CorrespondanceGlobaleHistorySerializer;
use App\Utils\DiffsInterface;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;
use Psr\Log\LoggerInterface;

class CorrespondanceGlobaleHistoryUpdatedHandler implements MessageHandlerInterface
{
	private $requestStack;
    private $security;
    private $session;
    private $translator;
    private $entityManager;
    private $correspondanceGlobaleHistorySerializer;
    private $logger;
    private $diffs;
    private $pSMFRepository;
    private $pSMFHistoryMailing;

    public function __construct(RequestStack $requestStack, EntityManagerInterface $entityManager, Security $security, CorrespondanceGlobaleHistorySerializer $correspondanceGlobaleHistorySerializer, SessionInterface $session, PSMFRepository $pSMFRepository, LoggerInterface $logger, TranslatorInterface $translator, DiffsInterface $diffs, PSMFHistoryMailing $pSMFHistoryMailing)
    {
    	$this->requestStack = $requestStack;
    	$this->security = $security;
    	$this->session = $session;
    	$this->logger = $logger;
    	$this->diffs = $diffs;
    	$this->translator = $translator;
        $this->entityManager = $entityManager;
        $this->pSMFRepository = $pSMFRepository;
        $this->correspondanceGlobaleHistorySerializer = $correspondanceGlobaleHistorySerializer;
        $this->pSMFHistoryMailing = $pSMFHistoryMailing;
    }

    public function __invoke(CorrespondanceGlobaleHistoryUpdated $correspondanceGlobaleHistoryUpdated)
    {
        $correspondanceGlobaleHistory = $correspondanceGlobaleHistoryUpdated->getCorrespondanceGlobaleHistory();
        $action = $correspondanceGlobaleHistoryUpdated->getAction();

        try {
        	$pSMFHistory = new PSMFHistory();
            $psmfs = $this->pSMFRepository->findBy(['isDeleted'=>false]);
            foreach ($psmfs as $psmf) {
                $pSMFHistory->addPsmf($psmf);
            }
	        $pSMFHistory->setCorrespondanceGlobale($correspondanceGlobaleHistory);
	        $pSMFHistory->setReason($correspondanceGlobaleHistory->getReason());
            $pSMFHistory->setAction($action);
	        $pSMFHistory->setCreateUser($this->security->getUser());
	        $pSMFHistory->setUpdateUser($this->security->getUser()); 
	        $pSMFHistory->setIp($this->requestStack->getCurrentRequest()->getClientIp());

            $diff = json_encode($this->diffs->diffsCorrespondance($correspondanceGlobaleHistoryUpdated->getOld(), $this->correspondanceGlobaleHistorySerializer->serialize($correspondanceGlobaleHistory)));
            
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
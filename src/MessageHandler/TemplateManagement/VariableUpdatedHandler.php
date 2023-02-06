<?php

namespace App\MessageHandler\TemplateManagement;

use App\Entity\PSMFManagement\PSMFHistory; 
use App\Mailer\PSMFHistoryMailing;
use App\Message\TemplateManagement\VariableUpdated;
use App\Repository\PSMFManagement\PSMFRepository;
use App\Serializer\TemplateManagement\VariableSerializer;
use App\Utils\DiffsInterface;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;
use Psr\Log\LoggerInterface;

class VariableUpdatedHandler implements MessageHandlerInterface
{
	private $requestStack;
    private $security;
    private $session;
    private $translator;
    private $entityManager;
    private $variableSerializer;
    private $logger;
    private $diffs;
    private $pSMFRepository;
    private $pSMFHistoryMailing;

    public function __construct(RequestStack $requestStack, EntityManagerInterface $entityManager, Security $security, VariableSerializer $variableSerializer, SessionInterface $session, PSMFRepository $pSMFRepository, LoggerInterface $logger, TranslatorInterface $translator, DiffsInterface $diffs, PSMFHistoryMailing $pSMFHistoryMailing)
    {
    	$this->requestStack = $requestStack;
    	$this->security = $security;
    	$this->session = $session;
    	$this->logger = $logger;
    	$this->diffs = $diffs;
    	$this->translator = $translator;
        $this->entityManager = $entityManager;
        $this->pSMFRepository = $pSMFRepository;
        $this->variableSerializer = $variableSerializer;
        $this->pSMFHistoryMailing = $pSMFHistoryMailing;
    }

    public function __invoke(VariableUpdated $variableUpdated)
    {
        $variable = $variableUpdated->getVariable();
        $action = $variableUpdated->getAction();
        
        try {
        	$pSMFHistory = new PSMFHistory();
            $psmfs = $this->pSMFRepository->findBy(['isDeleted'=>false]);
            foreach ($psmfs as $psmf) {
                $pSMFHistory->addPsmf($psmf);
            }              
	        $pSMFHistory->setVariable($variable);
	        $pSMFHistory->setReason($variable->getReason());
            $pSMFHistory->setAction($action);
	        $pSMFHistory->setCreateUser($this->security->getUser());
	        $pSMFHistory->setUpdateUser($this->security->getUser()); 
	        $pSMFHistory->setIp($this->requestStack->getCurrentRequest()->getClientIp());

            $diff = json_encode($this->diffs->diffs($variableUpdated->getOld(), $this->variableSerializer->serialize($variable)));
            
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
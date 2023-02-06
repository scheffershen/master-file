<?php

namespace App\Controller;

use App\Repository\PSMFManagement\PSMFRepository;
use App\Repository\PSMFManagement\PublishedDocumentRepository;
use App\Repository\TemplateManagement\SectionRepository;
use App\Repository\TemplateManagement\VariableRepository;
use App\Repository\UserManagement\ClientRepository;
use App\Repository\UserManagement\UserRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
* @codeCoverageIgnore
*/
class DashboardController extends AbstractController
{
    private $publishedDocumentRepository;
    private $pSMFRepository;
    private $variableRepository;
    private $sectionRepository;
    private $userRepository;
    private $clientRepository;

    public function __construct(PublishedDocumentRepository $publishedDocumentRepository, PSMFRepository $pSMFRepository, VariableRepository $variableRepository, SectionRepository $sectionRepository, UserRepository $userRepository, ClientRepository $clientRepository) 
    {
        $this->publishedDocumentRepository = $publishedDocumentRepository;
        $this->pSMFRepository = $pSMFRepository;
        $this->variableRepository = $variableRepository;
        $this->sectionRepository = $sectionRepository;
        $this->userRepository = $userRepository;
        $this->clientRepository = $clientRepository;
    }

    /**
     * @Route("/admin/dashboard", name="admin_dashboard", methods="GET")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function adminDashboard(Request $request): Response
    {
        return $this->render('Dashboard/admin_dashboard.html.twig', [
            'documents' => $this->publishedDocumentRepository->findBy(['isArchived'=>false], ['publicationDate'=>'DESC'], 5, 0),
            'variables' => $this->variableRepository->findEquivalencesLocales(),
            'psmfs' => $this->pSMFRepository->findBy(['isDeleted'=>false], ['createDate'=>'DESC'], 5, 0),
            'sections' => $this->sectionRepository->findBy(['isValid'=>true], ['updateDate'=>'DESC'], 5, 0),
            'systemes' => count($this->variableRepository->findEquivalencesSystemes()),
            'globales' => count($this->variableRepository->findEquivalencesGlobales()),
            'locales' => count($this->variableRepository->findEquivalencesLocales()),
            'users' => $this->userRepository->findBy(['isEnable'=>true], ['updateDate'=>'DESC'], 5, 0),
            'clients' => $this->clientRepository->findBy(['isDeleted'=>false], ['updateDate'=>'DESC'], 3, 0)
        ]);
    }
}
<?php

namespace App\Controller\PSMFManagement;

use App\Entity\PSMFManagement\PSMF;
use App\Entity\PSMFManagement\PublishedDocument;
use App\Entity\PSMFManagement\PSMFHistory;
use App\Repository\PSMFManagement\PSMFHistoryRepository;
use App\Repository\PSMFManagement\PublishedDocumentRepository;
use App\Repository\TemplateManagement\VariableRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

/**
 * @Route("/admin/psmfHistory")
 */
class PSMFHistoryController extends AbstractController
{
    private $pSMFHistoryRepository;
    private $publishedDocumentRepository;
    private $variableRepository;
    private $twig;

    public function __construct(PublishedDocumentRepository $publishedDocumentRepository, PSMFHistoryRepository $pSMFHistoryRepository, Environment $twig, VariableRepository $variableRepository) 
    {
        $this->twig = $twig;
        $this->variableRepository = $variableRepository;
    	$this->pSMFHistoryRepository = $pSMFHistoryRepository;
        $this->publishedDocumentRepository = $publishedDocumentRepository; 
    }

    /**
     * @Route("/history/{id}", requirements={"id"="\d+"}, name="admin_psmf_history")
     * @Security("is_granted('ROLE_SUPER_CONSULTANT')")
     */
    public function history(PSMF $psmf): Response
    {
        return $this->render('PSMFManagement/PSMFHistory/history.html.twig', [
            'psmf' => $psmf,
            'variables' => $this->variableRepository->findEquivalencesLocales(),
            'pSMFVariableHistories' => $this->pSMFHistoryRepository->findByVariableLastModification($psmf),
            'pSMFSectionHistories' => $this->pSMFHistoryRepository->findBySectionLastModification($psmf),
        ]);
    }

    /**
     * @Route("/history/download/{id}", name="admin_psmf_history_download")
     * @Security("is_granted('ROLE_SUPER_CONSULTANT')")
     */
    public function download(PSMF $psmf): Response
    {
        $content = $this->twig->render('PSMFManagement/PSMFHistory/history_excel.html.twig', [
            'pSMFVariableHistories' => $this->pSMFHistoryRepository->findByVariableLastModification($psmf),
            'pSMFSectionHistories' => $this->pSMFHistoryRepository->findBySectionLastModification($psmf)]);

        return new Response($content, 200, [
            'Content-Type' => 'application/force-download;charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="psmf_history_' . (new \Datetime())->format('d-M-Y H:i:s') . '.xls"'
        ]);         
    }

    /**
     * @Route("/archive/{psmf}/{publishedDocument}", name="admin_psmf_history_archive")
     * @Security("is_granted('ROLE_SUPER_CONSULTANT')")
     */
    public function archiveHistory(PSMF $psmf, PublishedDocument $publishedDocument): Response
    {
        // PublishedDocument->publicationDate()
        // 'publishedDocuments' => $this->publishedDocumentRepository->findBy(['psmf'=>$psmf], ['publicationDate'=>'DESC']),
        if ($publishedDocument->getVersion() > 1) {
            $old_version = $publishedDocument->getVersion() - 1;
            $old_publishedDocument = $this->publishedDocumentRepository->findOneBy(['psmf'=>$psmf, 'version'=>$old_version]);
            $pSMFVariableHistories = $this->pSMFHistoryRepository->findByVariableArchiveModification($psmf, $publishedDocument->getPublicationDate(),  $old_publishedDocument->getPublicationDate());
            $pSMFSectionHistories = $this->pSMFHistoryRepository->findBySectionArchiveModification($psmf, $publishedDocument->getPublicationDate(),  $old_publishedDocument->getPublicationDate());
        } else {
            $pSMFVariableHistories = $this->pSMFHistoryRepository->findByVariableArchiveModification($psmf, $publishedDocument->getPublicationDate(),  null);
            $pSMFSectionHistories = $this->pSMFHistoryRepository->findBySectionArchiveModification($psmf, $publishedDocument->getPublicationDate(),  null);
        }

        return $this->render('PSMFManagement/PSMFHistory/archive.html.twig', [
            'psmf' => $psmf,
            'publishedDocument' => $publishedDocument,
            'pSMFVariableHistories' => $pSMFVariableHistories,
            'pSMFSectionHistories' => $pSMFSectionHistories,
        ]);
    }

}

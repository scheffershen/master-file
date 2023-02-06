<?php

namespace App\Controller\PSMFManagement;

use App\Entity\LovManagement\Status;
use App\Entity\PSMFManagement\PSMF;
use App\Entity\PSMFManagement\PublishedDocument;
use App\Form\PSMFManagement\PublishedDocumentType;
use App\Form\PSMFManagement\PublishedDocumentPdfSigneType;
use App\Message\PSMFManagement\SendPublishedDocumentCreated;
use App\Manager\PSMFManagement\PSMFDocumentManager;
use App\Repository\PSMFManagement\PSMFRepository;
use App\Repository\PSMFManagement\PSMFHistoryRepository;
use App\Repository\PSMFManagement\PublishedDocumentRepository;
use App\Service\PSMFManagement\PSMFPublishedDocumentImporterService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\DBAL\Exception as DBALException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security as SymfonySecurity;

/**
 * @Route("/admin/publishedDocument")
 */
class PSMFPublishedDocumentController extends AbstractController
{
    private $pSMFDocumentManager;
    private $messageBus;
    private $translator;
    private $kernel;
    private $publishedDocumentRepository;
    private $pSMFRepository;
    private $pSMFHistoryRepository;
    private $entityManager;
    private $pSMFPublishedDocumentImporterService;

    public function __construct(PSMFDocumentManager $pSMFDocumentManager, PublishedDocumentRepository $publishedDocumentRepository, PSMFRepository $pSMFRepository, PSMFHistoryRepository $pSMFHistoryRepository, MessageBusInterface $messageBus, TranslatorInterface $translator, KernelInterface $kernel, EntityManagerInterface $entityManager, PSMFPublishedDocumentImporterService $pSMFPublishedDocumentImporterService)
    {
        $this->pSMFDocumentManager = $pSMFDocumentManager;
        $this->messageBus = $messageBus;
        $this->translator = $translator;     
        $this->kernel = $kernel;  
        $this->publishedDocumentRepository = $publishedDocumentRepository; 
        $this->pSMFRepository = $pSMFRepository;
        $this->pSMFHistoryRepository = $pSMFHistoryRepository;
        $this->entityManager = $entityManager;
        $this->pSMFPublishedDocumentImporterService = $pSMFPublishedDocumentImporterService;
    }

    /**
     * @Route("/archive", name="admin_published_document_archive", methods={"GET"})
     * @Security("is_granted('ROLE_UTILISATEUR') or is_granted('ROLE_CONSULTANT')")
     */
    public function archive(SymfonySecurity $security): Response
    {
        $archive = $this->entityManager->getRepository("App\Entity\LovManagement\Status")->findOneBy(['code'=>Status::ARCHIVE]);
        if ($security->isGranted('ROLE_ADMIN')) {
            return $this->render('PSMFManagement/PublishedDocument/archive.html.twig', [
                'psmfs' => $this->pSMFRepository->findPublishedDocumentByStatus(null, $archive)
            ]);
        } else {
            return $this->render('PSMFManagement/PublishedDocument/archive.html.twig', [
                'psmfs' => $this->pSMFRepository->findPublishedDocumentByStatus($this->getUser(), $archive),
            ]);
        }
    }

    /**
     * @Route("/actif", name="admin_published_document_actif", methods={"GET"})
     * @Security("is_granted('ROLE_UTILISATEUR') or is_granted('ROLE_CONSULTANT')")
     */
    public function actif(SymfonySecurity $security): Response
    {
        $actifs = $this->entityManager->getRepository("App\Entity\LovManagement\Status")->findByActifs();

        if ($security->isGranted('ROLE_ADMIN')) {
            return $this->render('PSMFManagement/PublishedDocument/actif.html.twig', [
                'psmfs' => $this->pSMFRepository->findPublishedDocumentActif(null, $actifs)
            ]);
        } else {
            return $this->render('PSMFManagement/PublishedDocument/actif.html.twig', [
                'psmfs' => $this->pSMFRepository->findPublishedDocumentActif($this->getUser(), $actifs)
            ]);
        }
    }

    /**
     * @Route("/new/{psmf}", name="admin_published_document_new", methods={"GET","POST"})
     * Security("is_granted('ROLE_USER')")
     * @IsGranted("PSMF_PUBLISH", subject="psmf", message="You cannot publish this PSMF.")
     */
    public function new(Request $request, PSMF $psmf): Response
    {      
        error_reporting(0);
        $publishedDocument = new PublishedDocument();
        $publishedDocument->setPSMF($psmf);

        $form = $this->createForm(PublishedDocumentType::class, $publishedDocument);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $publishedDocument->setAuthor($this->getUser());
                if ($psmf->getLastVersion()) {
                    $publishedDocument->setVersion($psmf->getLastVersion()->getVersion()+1);
                } else {
                    $publishedDocument->setVersion(1);
                }
                
                $pdfUri = $this->pSMFDocumentManager->pSMFGenerator($psmf, PSMF::PDF);
                $wordUri = $this->pSMFDocumentManager->pSMFGenerator($psmf, PSMF::WORD);
                $htmlUri = $this->pSMFDocumentManager->pSMFGenerator($psmf, PSMF::HTML);

                $publishedDocument->setHtmlUri($htmlUri);  
                $publishedDocument->setPdfUri($pdfUri); 
                $publishedDocument->setWordUri($wordUri); 
                $publishedDocument->setUpdateSectionDetails(json_encode($this->pSMFDocumentManager->getSectionDetails($psmf, PSMF::HTML))); 
                $publishedDocument->setUpdateVariableDetails(json_encode($this->pSMFDocumentManager->getVariableDetails($psmf, PSMF::HTML))); 

                $published = $this->entityManager->getRepository("App\Entity\LovManagement\Status")->findOneBy(['code'=>Status::PUBLISHED]);  
                $publishedDocument->setStatus($published); 

                $this->entityManager->persist($publishedDocument);
                $this->entityManager->flush();

                $this->messageBus->dispatch(new SendPublishedDocumentCreated($publishedDocument));
                
                $this->addFlash('success', $this->translator->trans('document.flash.created'));

                return $this->redirectToRoute('admin_published_document_actif');
            } catch (DBALException $exception) {
                $this->addFlash('error', $exception->getMessage());
            } catch (\Exception $exception) {
                $this->addFlash('error', $exception->getMessage());
            }

        }

        return $this->render('PSMFManagement/PublishedDocument/new.html.twig', [
            'form' => $form->createView(),
            'psmf' => $psmf,
            'publishedDocuments' => $this->publishedDocumentRepository->findBy(['psmf'=>$psmf], ['publicationDate'=>'DESC']),
            'pSMFVariableHistories' => $this->pSMFHistoryRepository->findByVariableLastModification($psmf),
            'pSMFSectionHistories' => $this->pSMFHistoryRepository->findBySectionLastModification($psmf),            
        ]);
    }

    /**
     * @Route("/importer/{publishedDocument}", name="admin_published_document_importer", methods={"GET","POST"})
     * 
     * @IsGranted("ROLE_ADMIN")
     */
    public function importer(Request $request, PublishedDocument $publishedDocument): Response
    {
        $form = $this->createForm(PublishedDocumentPdfSigneType::class, $publishedDocument);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($result = $this->pSMFPublishedDocumentImporterService->importer($form, $publishedDocument)) {
                return $this->redirectToRoute('admin_published_document_actif');
            }
        }

        return $this->render('PSMFManagement/PublishedDocument/importer.html.twig', [
            'form' => $form->createView(),
            'publishedDocument' => $publishedDocument          
        ]);
    }

    /**
     * @Route("/download/{publishedDocument}/{format}", defaults={"format": "html"}, name="admin_published_document_download", methods={"GET","POST"})
     * FM28|Exporter les documents applicables
     * @IsGranted("ROLE_USER")
     */
    public function download(PublishedDocument $publishedDocument, string $format="html"): Response
    {
        $fs = new Filesystem();

        switch ($format) {
            case PSMF::WORD:
                $filePath = $this->kernel->getProjectDir() . '/PSMD/'. basename($publishedDocument->getWordUri());
                break;
            case PSMF::PDF:
                $filePath = $this->kernel->getProjectDir() . '/PSMD/'. basename($publishedDocument->getPdfUri());
                break;
            case PSMF::PDF_SIGNE:
                $filePath = $this->kernel->getProjectDir() . '/PSMD_SIGNE/'. basename($publishedDocument->getPdfSigneUri());
                if (!$fs->exists($filePath)) {
                    $filePath = $this->kernel->getProjectDir() . '/data/'. basename($publishedDocument->getPdfSigneUri());
                    if ($fs->exists($filePath)) {
                        $fs->copy($this->kernel->getProjectDir() . '/data/'. basename($publishedDocument->getPdfSigneUri()), $this->kernel->getProjectDir() . '/PSMD_SIGNE/'. basename($publishedDocument->getPdfSigneUri()), true);
                    }
                }
                break;                
            case PSMF::HTML:
            default:
                $filePath = $this->kernel->getProjectDir() . '/PSMD/'. basename($publishedDocument->getHtmlUri());
                break;            
        }
        
        if ($fs->exists($filePath)) {
            $nbDownload = $publishedDocument->getNbDownload();
            $publishedDocument->setNbDownload($nbDownload++);
            $publishedDocument->setPdfDownloadDate(new \DateTime());
            $publishedDocument->setPdfDownloadUser($this->getUser());

            $published = $this->entityManager->getRepository("App\Entity\LovManagement\Status")->findOneBy(['code'=>Status::PUBLISHED]);           
            if ($publishedDocument->getStatus() == $published) {
                $downloaded = $this->entityManager->getRepository("App\Entity\LovManagement\Status")->findOneBy(['code'=>Status::DOWNLOADED]);  
                $publishedDocument->setStatus($downloaded); 
            }
            
            try {   
                $this->entityManager->persist($publishedDocument);
                $this->entityManager->flush();
            } catch (DBALException $exception) {
                return new Response($exception->getMessage());
            } catch (\Exception $exception) {
                return new Response($exception->getMessage());
            }

            $response = new BinaryFileResponse($filePath);
                $response->headers->set('Content-Type', mime_content_type($filePath));
                $response->setContentDisposition(
                    ResponseHeaderBag::DISPOSITION_INLINE,
                    basename($filePath)
                );
                return $response;            
        } 

        $filePath = $this->kernel->getProjectDir() . '/data/'. PSMF::NOT_FOUND_IMAGE;
        
        if ($fs->exists($filePath)) {                        
            $response = new BinaryFileResponse($filePath);
                $response->headers->set('Content-Type', mime_content_type($filePath));
                $response->setContentDisposition(
                    ResponseHeaderBag::DISPOSITION_INLINE,
                    basename($filePath)
                );
                return $response;            
        } 
        throw new FileNotFoundException($filePath);
    }
 
    /**
     * @Route("/downloadlocale", name="admin_published_document_download_locale", methods={"GET","POST"})
     * @IsGranted("ROLE_USER")
     */
    public function locale(Request $request): Response
    {
        $docfileName = $request->get('docfileName');
        $filePath = $this->kernel->getProjectDir() . '/var/log/'.$docfileName;
        
        $fs = new Filesystem();
        if ($fs->exists($filePath)) {       
            $response = new BinaryFileResponse($filePath);
                $response->headers->set('Content-Type', mime_content_type($filePath));
                $response->setContentDisposition(
                    ResponseHeaderBag::DISPOSITION_INLINE,
                    basename($filePath)
                );
                return $response;            
        } 

        $filePath = $this->kernel->getProjectDir() . '/data/not-found.png';
        
        if ($fs->exists($filePath)) {       
            $response = new BinaryFileResponse($filePath);
                $response->headers->set('Content-Type', mime_content_type($filePath));
                $response->setContentDisposition(
                    ResponseHeaderBag::DISPOSITION_INLINE,
                    basename($filePath)
                );
                return $response;            
        } 
        throw new FileNotFoundException($filePath);
    } 
}

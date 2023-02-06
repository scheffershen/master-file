<?php

declare(strict_types=1);

namespace App\Service\PSMFManagement;

use App\Entity\LovManagement\Status;
use App\Entity\PSMFManagement\PublishedDocument;
use App\Entity\PSMFManagement\PublishedDocumentImportHistory;
use App\Service\AbstractService;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class PSMFPublishedDocumentImporterService extends AbstractService
{
    private $translator;
    private $entityManager;
    private $kernel;
    private $slugger;
    private $security;

    public function __construct(
        Security $security,
        EntityManagerInterface $entityManager,
        ContainerInterface $container,
        KernelInterface $kernel,
        SluggerInterface $slugger,
        TranslatorInterface $translator
    ) {
        parent::__construct($container);
        $this->security = $security;
        $this->entityManager = $entityManager;
        $this->kernel = $kernel;
        $this->slugger = $slugger;
        $this->translator = $translator;
    }  

    public function importer(FormInterface &$form, PublishedDocument &$publishedDocument): bool
    {
        $form_passed = true;
        $ds = DIRECTORY_SEPARATOR;
        $data_path = $this->kernel->getProjectDir(). $ds .'PSMD_SIGNE'. $ds;

        $uploadFile = $form->get('pdfSigne')->getData();
        if ($uploadFile) {
            $originalFilename = pathinfo($uploadFile->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $this->slugger->slug($originalFilename);
            $newFilename = $safeFilename.'-'.date("YmdHi").'.'.$uploadFile->guessExtension();                             
            try {
                $uploadFile->move($data_path, $newFilename);
                $publishedDocument->setPdfSigneUri($newFilename);
            } catch (FileException $e) {
                $this->addFlash('error', $e->getMessage());
                $form_passed = false;
            }
        }

        if ($form_passed) {
			return $this->save($publishedDocument);
        }
        return false;
    }

    private function save(PublishedDocument $publishedDocument): bool
    {
        try {
            $publishedDocument->setPdfSigneUploadUser($this->security->getUser()); 
            $publishedDocument->setPdfSigneUploadDate(new \DateTime());

            $applicable = $this->entityManager->getRepository("App\Entity\LovManagement\Status")->findOneBy(['code'=>Status::APPLICABLE]);

            $publishedDocument->setStatus($applicable);

            $this->entityManager->persist($publishedDocument);
            
            $publishedDocumentImportHistory = new PublishedDocumentImportHistory();
            $publishedDocumentImportHistory->setPublishedDocument($publishedDocument);
            $publishedDocumentImportHistory->setAuteur($this->security->getUser());
            $publishedDocumentImportHistory->setImportDate(new \DateTime());
            $publishedDocumentImportHistory->setUri($publishedDocument->getPdfSigneUri());
            $publishedDocumentImportHistory->setVersion($publishedDocument->getVersion());
            $publishedDocumentImportHistory->setCommentaire($publishedDocument->getPdfSigneComentary());

            $this->entityManager->persist($publishedDocumentImportHistory);
            $this->entityManager->flush(); 
            
            $this->addFlash('success', $this->translator->trans('document.flash.uploaded'));

            return true;
        } catch (DBALException $exception) {
            $this->addFlash('error', $exception->getMessage());
        } catch (\Exception $exception) {
            $this->addFlash('error', $exception->getMessage());
        }

        return false;
    }  

}
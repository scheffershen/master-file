<?php

namespace App\MessageHandler\PSMFManagement;

use App\Entity\LovManagement\Status;
use App\Entity\PSMFManagement\PublishedDocument;
use App\Mailer\Mailer;
use App\Message\PSMFManagement\SendPublishedDocumentCreated;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class SendPublishedDocumentCreatedHandler implements MessageHandlerInterface
{
    /**
     * @var Mailer
     */
    private $mailer;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var UrlGeneratorInterface
     */
    private $router;

    /**
     * @var Environment
     */
    private $twig;

    private $parameter;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(Environment $twig, Mailer $mailer, TranslatorInterface $translator, ParameterBagInterface $parameter, UrlGeneratorInterface $router, EntityManagerInterface $entityManager)
    {
        $this->mailer = $mailer;
        $this->translator = $translator;
        $this->twig = $twig;
        $this->router = $router;
        $this->parameter = $parameter;
        $this->entityManager = $entityManager;
    }

    public function __invoke(SendPublishedDocumentCreated $sendPublishedDocumentCreated)
    {
        // $this->mailer->sendMail($this->getSender(), $this->parameter->get('admin_email'), $this->getSubject(), $this->getBody($sendPublishedDocumentCreated->getPublishedDocument()));
        
        $publishedDocument = $sendPublishedDocumentCreated->getPublishedDocument();

        // from version 2, we update last published document status to "archive"
        if ($publishedDocument->getVersion() > 1) {
      
            $lastPublishedDocument = $this->entityManager->getRepository(PublishedDocument::class)->findOneBy(['psmf'=>$publishedDocument->getPsmf(), 'version' => $publishedDocument->getVersion() - 1 ]);
            $archived = $this->entityManager->getRepository(Status::class)->findOneBy(['code' => Status::ARCHIVE]);

            if ($lastPublishedDocument && $lastPublishedDocument->getStatus() != $archived) {                
                $lastPublishedDocument->setStatus($archived);
                $this->entityManager->persist($lastPublishedDocument);
                $this->entityManager->flush();
            }
        }
    }

    private function getSender(): array
    {
        return [$this->parameter->get('admin_email')];
    }

    private function getSubject(): string
    {
        return $this->translator->trans('document.new_document');
    }

    private function getBody(PublishedDocument $publishedDocument): ?string
    {
        return $this->twig->render('PSMFManagement/emails/document_created.html.twig', [
            'publishedDocument' => $publishedDocument,
        ]);
    }
}

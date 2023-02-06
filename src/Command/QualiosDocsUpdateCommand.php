<?php

namespace App\Command;

use App\Entity\Plateforme;
use App\Mailer\Mailer;
use App\Entity\QualiosManagement\Document;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class QualiosDocsUpdateCommand extends Command
{
    protected static $defaultName = 'psmf:qualios-docs-update';
    
    /**
     * @var EntityManagerInterface
     */
    private $em;    
    private $mailer;
    private $parameter;

    /*
    * Mise à jour la base des donnèes
    * $ php /var/www/psmf/bin/console psmf:qualios-docs-update /var/www/psmf/data/qualios.xml
    */
    public function __construct(Mailer $mailer, ParameterBagInterface $parameter, EntityManagerInterface $entityManager)
    {
        $this->mailer = $mailer;
        $this->parameter = $parameter;
        $this->em = $entityManager;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Add a short description for your command')
            ->setHelp('This command allows you to send alert mail for the history of the psmf modifications')
            ->addArgument('qualios', InputArgument::REQUIRED, 'Qualios xml file')        
            ;            
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $qualios = $input->getArgument('qualios');

        // check if data/qualios.xml existed
        if (!file_exists($qualios)) {
            $io->error(sprintf('File "%s" not found', $qualios));
            // send a mail to the admin
            $this->mailer->sendMail([$this->parameter->get('admin_email')], $this->parameter->get('admin_email'), sprintf('File "%s" not found', $qualios), "File not found");
            return 1;
        }
        $io->note(sprintf('File "%s" found', $qualios));

        // read qualios xml file and get the list of documents
        if ($xml = simplexml_load_file($qualios)) {
            // get all documents
            $documents = $xml->xpath('//document');
            $io->note(sprintf('%d documents found', count($documents)));
            $io->progressStart(count($documents));
            $i = 0;
            $errors = "";

            // update documents
            foreach ($documents as $document) {
                $i++;
                $io->progressAdvance();
                $reference = (string)$document->Reference;
                $version = (int)$document->Version;
                $ajout = true;
                
                // if the document is not existing, create it
                if (null === $doc = $this->em->getRepository("App\Entity\QualiosManagement\Document")->findOneBy(['reference'=>$reference]) ) {
                    $doc = new Document();
                    $doc->setReference($reference);
                } elseif ($version < $doc->getVersion() ) {
                    $ajout = false;
                    $doc->setVersion($version);
                }

                if ($ajout) {
                    $doc->setVersion($version);
                    $doc->setTitle((string)$document->Title);
                    $doc->setActivityDate(new \DateTime((string)$document->ActivityDate));
                    $doc->setCreateDate(new \DateTime((string)$document->CreateDate));
                    $doc->setValidityDate(new \DateTime((string)$document->ValidityDate));
                    $doc->setNatureOfChange((string)$document->NatureOfChanges);
                    $doc->setObject((string)$document->Object);
                    $doc->setSimpleOrDynamic((bool)$document->SimpleOrDynamic);
                    $doc->setArchiveDate(new \DateTime((string)$document->ArchiveDate));
                    $doc->setReviewDate(new \DateTime((string)$document->ReviewDate));
                    $doc->setReplayDate((int)$document->ReplayDate);
                    $doc->setSubmitDate(new \DateTime((string)$document->SubmitDate));
                    $doc->setSignDelay((int)$document->SignDelay);
                    $doc->setLateSignAlert((bool)$document->LateSignAlert);
                    $doc->setFormationAccessibility((bool)$document->formationAccessibility);
                    $doc->setActionWhenArchive((int)$document->ActionWhenArchive);
                    $doc->setArchivePeriod((bool)$document->ArchivePeriod);
                    $doc->setArchivePeriodAlert((bool)$document->ArchivePeriodAlert);
                    $doc->setReviewAlert((bool)$document->ReviewAlert);
                    $doc->setDocRefQualifForm((string)$document->docRefQualifForm);
                    $doc->setRappelQualifForm((bool)$document->rappelQualifForm);
                    $doc->setSignByLevel((bool)$document->SignByLevel);
                    $doc->setNbLevel((int)$document->NbLevel);
                    $doc->setDocCode((string)$document->docCode);
                    $doc->setTitleComposition((string)$document->titleComposition);
                    $doc->setTestMode((string)$document->TestMode);
                    $doc->setDelayValidity((bool)$document->DelayValidity);
                    $doc->setDelayAR((bool)$document->DelayAR);
                    $doc->setAttachmentAsBody((bool)$document->AttachmentAsBody);            
                    $type = $this->em->getRepository("App\Entity\LovManagement\QualiosType")->findOneBy(['code'=>(int)$document->Type]);
                    $status  = $this->em->getRepository("App\Entity\LovManagement\QualiosStatus")->findOneBy(['code'=>(int)$document->Status]);
                    if ($type && $status) {
                        $doc->setType($type);
                        $doc->setStatus($status);
                        try {
                            $this->em->persist($doc);
                        } catch (\Throwable $exception) {
                            $io->error(sprintf('Error during the update of the document %s', $reference));
                            $io->error($exception->getMessage());                 
                        }
                    } else {
                        $io->error(sprintf('Type %d or status %d not found for document %s', (int)$document->Type, (int)$document->Status, $reference));
                        $errors .= sprintf('Type %d or status %d not found for document %s', (int)$document->Type, (int)$document->Status, $reference);
                    }
                    $this->em->flush();
                }

            }
            
            $io->progressFinish();

            if (!empty($errors)) {
                // send a mail to admin only
                $this->mailer->sendMail([$this->parameter->get('admin_email')], $this->parameter->get('admin_email'), "Type or status not found", sprintf('Type %d or status %d not found for document %s', (int)$document->Type, (int)$document->Status, $reference));
            } 

            if ($plateforme = $this->em->getRepository('App\Entity\Plateforme')->findOneBy(['code' => Plateforme::PSMF]) ) {
                $plateforme->setQualiosLastUpdate(new \DateTime());
                $this->em->persist($plateforme);
                $this->em->flush();
            }

        } else {
            $io->error(sprintf('Error during the parsing of the file %s', $qualios));
            // send a mail to admin only
            $this->mailer->sendMail([$this->parameter->get('admin_email')], $this->parameter->get('admin_email'), "qualios file is not xml", sprintf('Error during the parsing of the file %s', $qualios));
            return 1;            
        }

        return Command::SUCCESS;
    }
}

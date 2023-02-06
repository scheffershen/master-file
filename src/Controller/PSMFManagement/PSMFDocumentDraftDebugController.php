<?php

namespace App\Controller\PSMFManagement;

use App\Entity\TemplateManagement\Template;
use App\Entity\TemplateManagement\Section;
use App\Entity\PSMFManagement\PSMF;
use App\Library\HtmlToDoc;
use App\Manager\PSMFManagement\PSMFDocumentManager;
use App\Repository\TemplateManagement\SectionRepository;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Shared\Html;
use PhpOffice\PhpWord\SimpleType\Jc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Spipu\Html2Pdf\Exception\ExceptionFormatter;
use Spipu\Html2Pdf\Exception\Html2PdfException;
use Spipu\Html2Pdf\Html2Pdf;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

/**
 * @codeCoverageIgnore
 * @Route("/admin/psmf")
 */
class PSMFDocumentDraftDebugController extends AbstractController
{
    protected $pSMFDocumentManager;
    protected $sectionRepository;
    protected $twig;

    public function __construct(PSMFDocumentManager $pSMFDocumentManager, SectionRepository $sectionRepository, Environment $twig)
    {
        $this->pSMFDocumentManager = $pSMFDocumentManager;
        $this->sectionRepository = $sectionRepository;
        $this->twig = $twig;
    }

    /**
     * draft v2 without libs, word export test 1, native solution    
     * @Route("/draft2/{psmf}/{format}", name="admin_psmf_draft2", methods={"GET"})
     * @Security("is_granted('ROLE_USER')")
     */
    public function draft2(PSMF $psmf, string $format, SectionRepository $sectionRepository): Response
    {          
        // sections
        $header = $sectionRepository->find(Section::HEADER_ID);
        $footer = $sectionRepository->find(Section::FOOTER_ID);
        $sections = $sectionRepository->findBy([
                    'template' => Template::TEMPLATE_ID,
                    'parent' => NULL,
                    'isDeleted' => false
                ],[
                    'position' => 'ASC',
                ]);  
       
        $contenu = "";
        foreach ($sections as $section) {
            if ($section->getId() > 2) {
                $contenu .= $this->pSMFDocumentManager->sectionParser($section, $section->getContenu())."<hr/>";
                foreach ($section->getSections() as $subSection) {
                    $contenu .= $this->pSMFDocumentManager->sectionParser($subSection, $subSection->getContenu());
                    foreach ($subSection->getSections() as $_subSection) {
                        $contenu .= $this->pSMFDocumentManager->sectionParser($_subSection, $_subSection->getContenu());
                        
                    }
                }
            }
        }  
        $contenu = $this->pSMFDocumentManager->systemesParser($psmf, $contenu);
        $contenu = $this->pSMFDocumentManager->globalesParser($contenu);
        $contenu = $this->pSMFDocumentManager->qualiosParser($contenu);
        $contenu = $this->pSMFDocumentManager->localesParser($psmf, $contenu);

        // create the response             
        $response = new Response(utf8_decode($contenu));
                
        // adding headers
        $response->headers->set('Content-Type', 'text/vnd.ms-word; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment;filename='.$psmf->getTitle() . '_' . (new \DateTime())->format('d-M-Y H:i:s') .'.doc');

        return $response;       
    }  

    /**
     * draft v1 with PhpWord, phpword solution no march pas !!!!
     * @Route("/draft1/{psmf}/{format}", name="admin_psmf_draft1", methods={"GET"})
     * @Security("is_granted('ROLE_USER')")
     */
    public function draft1(PSMF $psmf, string $format, SectionRepository $sectionRepository): Response
    {       
        // sections
        $header = $sectionRepository->find(Section::HEADER_ID);
        $footer = $sectionRepository->find(Section::FOOTER_ID);
        $sections = $sectionRepository->findBy([
                    'template' => Template::TEMPLATE_ID,
                    'parent' => NULL,
                    'isDeleted' => false
                ],[
                    'position' => 'ASC',
                ]);
        
        $phpWord = new PhpWord();

        $multilevelStyle = 'multilevel';
        $phpWord->addNumberingStyle(
            $multilevelStyle,
            [
                'type' => 'multilevel',
                'levels' => [
                    ['format' => 'decimal', 'text' => '%1.', 'left' => 360, 'hanging' => 360, 'tabPos' => 360],
                    ['format' => 'upperLetter', 'text' => '%2.', 'left' => 720, 'hanging' => 360, 'tabPos' => 720],
                ],
            ]
        );

        $section = $phpWord->addSection();

        $header = $section->addHeader();
        $header->addText(html_entity_decode($header->getContenu(), ENT_QUOTES | ENT_XML1, 'UTF-8'), ['size' => 14, 'color' => 'black', 'bold' => true]);

        // Add footer
        $footer = $section->addFooter();
        $footer->addText(html_entity_decode($footer->getContenu(), ENT_QUOTES | ENT_XML1, 'UTF-8'), null, array('alignment' => Jc::CENTER));

        foreach ($sections as $s) {
            if ($s->getId() > 2) {
                Html::addHtml($section, html_entity_decode($s->getContenu(), ENT_QUOTES | ENT_XML1, 'UTF-8'), false, false);
                if (count($s->getSections()) > 0 ) {
                    foreach ($s->getSections() as $_s) {
                        Html::addHtml($section, html_entity_decode($_s->getContenu(), ENT_QUOTES | ENT_XML1, 'UTF-8'), false, false);
                        foreach ($_s->getSections() as $__s) {
                            Html::addHtml($section, html_entity_decode($__s->getContenu(), ENT_QUOTES | ENT_XML1, 'UTF-8'), false, false);
                        }                         
                    }    
                }

                $section->addPageBreak();
            }
        }    

        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');

        $today = new \DateTime();

        $fileName = $psmf->getTitle() . '_' . $today->format('d-M-Y H:i:s') . '.docx';
        $temp_file = tempnam(sys_get_temp_dir(), $fileName);

        //ecriture du fichier dans un chemin temporel
        $objWriter->save($temp_file);

        // transfert du fichier tempaire commme pièce jointe
        $response = new BinaryFileResponse($temp_file);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $fileName);

        return $response;        
    }  

}
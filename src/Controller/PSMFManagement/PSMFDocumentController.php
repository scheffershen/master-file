<?php

namespace App\Controller\PSMFManagement;

use App\Entity\TemplateManagement\Template;
use App\Entity\TemplateManagement\Section;
use App\Entity\TemplateManagement\Variable;
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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Twig\Environment;

/**
 * @codeCoverageIgnore 
 * @Route("/admin/psmf")
 */
class PSMFDocumentController extends AbstractController
{
    private $pSMFDocumentManager;
    private $sectionRepository;
    private $twig;
    private $slugger;
    private $kernel;

    public function __construct(SluggerInterface $slugger, PSMFDocumentManager $pSMFDocumentManager, SectionRepository $sectionRepository, Environment $twig, KernelInterface $kernel)
    {
        $this->pSMFDocumentManager = $pSMFDocumentManager;
        $this->sectionRepository = $sectionRepository;
        $this->twig = $twig;
        $this->slugger = $slugger;
        $this->kernel = $kernel;
    }

    /**
     * draft v3 with HtmlToDoc   
     * @codeCoverageIgnore  
     * @Route("/draft1/{psmf}/{format}", name="admin_psmf_draft1", methods={"GET"})
     * @Security("is_granted('ROLE_USER')")
     */
    public function draft(Request $request, PSMF $psmf, string $format): Response
    {          
        // sections
        $header = $this->sectionRepository->find(Section::HEADER_ID);
        $footer = $this->sectionRepository->find(Section::FOOTER_ID);
        $sections = $this->sectionRepository->findBy([
                    'template' => Template::TEMPLATE_ID,
                    'parent' => NULL,
                    'isValid' => true,
                    'isDeleted' => false
                ],[
                    'position' => 'ASC',
                ]);

        $contenu = ""; $pages = []; $i = 0;
        switch ($format) {
            case PSMF::WORD:
                foreach ($sections as $section) {
                    if ($section->getId() > 2) {
                        $contenu .= $this->pSMFDocumentManager->sectionParser($section, $section->getContenu());
                        foreach ($section->getSections() as $subSection) {
                            if ($subSection->getIsValid()) {
                                //$contenu .= "<br>";
                                $contenu .= $this->pSMFDocumentManager->sectionParser($subSection, $subSection->getContenu());
                                foreach ($subSection->getSections() as $_subSection) {
                                    if ($_subSection->getIsValid()) {
                                        //$contenu .= "<br>";
                                        $contenu .= $this->pSMFDocumentManager->sectionParser($_subSection, $_subSection->getContenu());
                                    }
                                }  
                            }                          
                        }
                    }
                }  
                $contenu = $this->pSMFDocumentManager->systemesParser($psmf, $contenu, $format);
                $contenu = $this->pSMFDocumentManager->globalesParser($contenu, $format);
                $contenu = $this->pSMFDocumentManager->qualiosParser($contenu);
                $contenu = $this->pSMFDocumentManager->localesParser($psmf, $contenu, $format);  
                $contenu = $this->pSMFDocumentManager->tableContentsParser($sections, $contenu);

                $template = $this->twig->createTemplate($contenu);
                $contenu = $template->render(['psmf' => $psmf]);

                try {
                    $htd = new HtmlToDoc();        
                    $htd->setTitle($psmf->getTitle());
                    $htd->setHeader($header->getContenu());
                    $htd->createDoc($contenu, $psmf->getTitle() . '_' . (new \DateTime())->format('d-M-Y H:i:s'), 1); 
                } catch (\Throwable $exception) {
                    return new Response($exception->getMessage());
                }                      
                break;
            case PSMF::PDF:
                foreach ($sections as $section) {
                    if ($section->getId() > 2 ) {
                        $pages[$i] = $this->pSMFDocumentManager->sectionParser($section, $section->getContenu());
                        foreach ($section->getSections() as $subSection) {
                            if ($subSection->getIsValid()) {
                                //$pages[$i] .= "<br>";
                                $pages[$i] .= $this->pSMFDocumentManager->sectionParser($subSection, $subSection->getContenu());
                                foreach ($subSection->getSections() as $_subSection) {
                                    if ($_subSection->getIsValid()) {
                                        //$pages[$i] .= "<br>";
                                        $pages[$i] .= $this->pSMFDocumentManager->sectionParser($_subSection, $_subSection->getContenu());
                                    }
                                }                                
                            }
                        }
                        $pages[$i] = $this->pSMFDocumentManager->systemesParser($psmf, $pages[$i], $format);
                        $pages[$i] = $this->pSMFDocumentManager->globalesParser($pages[$i], $format);
                        $pages[$i] = $this->pSMFDocumentManager->qualiosParser($pages[$i]);
                        $pages[$i] = $this->pSMFDocumentManager->localesParser($psmf, $pages[$i], $format); 
                        $pages[$i] = $this->pSMFDocumentManager->tableContentsParser($sections, $pages[$i]);
                        $template = $this->twig->createTemplate($pages[$i]);
                        $pages[$i] = $template->render(['psmf' => $psmf]);
                        //$pages[$i] = str_replace(["&quot;", "&amp;", "article"], ["\"", "&", "span"], $pages[$i]);
                        $i++;
                    }
                }  

                $head = $this->pSMFDocumentManager->systemesParser($psmf, $header->getContenu(), $format);
                $head = $this->pSMFDocumentManager->globalesParser($head, $format);
                $head = $this->pSMFDocumentManager->qualiosParser($head);
                $head = $this->pSMFDocumentManager->localesParser($psmf, $head, $format); 

                $foot = $this->pSMFDocumentManager->systemesParser($psmf, $footer->getContenu(), $format);
                $foot = $this->pSMFDocumentManager->globalesParser($foot, $format);
                $foot = $this->pSMFDocumentManager->qualiosParser($foot);
                $foot = $this->pSMFDocumentManager->localesParser($psmf, $foot, $format);

                $contenu = $this->twig->render('PSMFManagement/PSMF/draft/pdf_draft.html.twig', [
                        'header' => $head,
                        'footer' => $foot,
                        'pages' => $pages
                    ]
                );
                //return new Response($contenu);

                if ($request->get('debug')) return new Response($contenu);

                try {
                    $html2pdf = new Html2Pdf('P', 'A4', 'fr');
                    $html2pdf->pdf->SetDisplayMode('real');
                    $html2pdf->setTestTdInOnePage(false);
                    $html2pdf->writeHTML($contenu);
                    $html2pdf->Output($psmf->getTitle() . '_' . (new \DateTime())->format('d-M-Y H:i:s'). '.pdf', 'D');
                } catch (Html2PdfException $e) {
                    $html2pdf->clean();
                    return new Response((new ExceptionFormatter($e))->getHtmlMessage());
                }
                break;
            case PSMF::HTML:
            default:
                foreach ($sections as $section) {
                    if ($section->getId() > 2) {
                        $pages[$i] = $this->pSMFDocumentManager->sectionParser($section, $section->getContenu());
                        foreach ($section->getSections() as $subSection) {
                            if ($subSection->getIsValid()) {
                                //$pages[$i] .= "<br>";
                                $pages[$i] .= $this->pSMFDocumentManager->sectionParser($subSection, $subSection->getContenu());
                                foreach ($subSection->getSections() as $_subSection) {
                                    if ($_subSection->getIsValid()) {
                                        //$pages[$i] .= "<br>";
                                        $pages[$i] .= $this->pSMFDocumentManager->sectionParser($_subSection, $_subSection->getContenu());
                                    }
                                }                                
                            }
                        }
                        $pages[$i] = $this->pSMFDocumentManager->systemesParser($psmf, $pages[$i], $format);
                        $pages[$i] = $this->pSMFDocumentManager->globalesParser($pages[$i], $format);
                        $pages[$i] = $this->pSMFDocumentManager->qualiosParser($pages[$i]);
                        $pages[$i] = $this->pSMFDocumentManager->localesParser($psmf, $pages[$i], $format);  
                        $pages[$i] = $this->pSMFDocumentManager->tableContentsParser($sections, $pages[$i]);
                        $template = $this->twig->createTemplate($pages[$i]);
                        $pages[$i] = $template->render(['psmf' => $psmf]);                                            
                        $i++;
                    }
                }  
                $head = $this->pSMFDocumentManager->systemesParser($psmf, $header->getContenu(), $format);
                $head = $this->pSMFDocumentManager->globalesParser($head, $format);
                $head = $this->pSMFDocumentManager->qualiosParser($head);
                $head = $this->pSMFDocumentManager->localesParser($psmf, $head, $format);

                $foot = $this->pSMFDocumentManager->systemesParser($psmf, $footer->getContenu(), $format);
                $foot = $this->pSMFDocumentManager->globalesParser($foot, $format);
                $foot = $this->pSMFDocumentManager->qualiosParser($foot);
                $foot = $this->pSMFDocumentManager->localesParser($psmf, $foot, $format);

                return $this->render('PSMFManagement/PSMF/draft/html_draft.html.twig', [
                    'header' => $head,
                    'footer' => $foot,
                    'pages' => $pages
                ]);
                break;                
        }

        return new Response();
    }  

    /**
     * draft v3 with HtmlToDoc
     * @codeCoverageIgnore      
     * @Route("/draftcorrespondance1/{id}/{psmf}/{format}", name="admin_psmf_correspondance_draft1", methods={"GET"})
     * @Security("is_granted('ROLE_USER')")
     */
    public function correspondance(Request $request, Variable $variable, PSMF $psmf, string $format): Response
    {          
        $header = $this->sectionRepository->find(Section::HEADER_ID);
        $footer = $this->sectionRepository->find(Section::FOOTER_ID);

        switch ($format) {
            case PSMF::WORD:
                $contenu = $this->pSMFDocumentManager->correspondanceLocale($psmf, $variable);
                $contenu = $this->pSMFDocumentManager->systemesParser($psmf, $contenu, $format);
                $contenu = $this->pSMFDocumentManager->globalesParser($contenu, $format);
                $contenu = $this->pSMFDocumentManager->qualiosParser($contenu);
                $contenu = $this->pSMFDocumentManager->localesParser($psmf, $contenu, $format); 

                $template = $this->twig->createTemplate($contenu);
                $contenu = $template->render(['psmf' => $psmf]);

                try {
                    $htd = new HtmlToDoc();        
                    $htd->setTitle($variable->getLabel());
                    $htd->setHeader($header->getContenu());
                    $htd->createDoc($contenu, $this->slugger->slug($variable->getLabel()) . '_' . (new \DateTime())->format('d-M-Y H:i:s'), 1); 
                } catch (\Throwable $exception) {
                    return new Response($exception->getMessage());
                }                      
                break;
            case PSMF::PDF:
                $page = $this->pSMFDocumentManager->correspondanceLocale($psmf, $variable);
                $page = $this->pSMFDocumentManager->systemesParser($psmf, $page, $format);
                $page = $this->pSMFDocumentManager->globalesParser($page, $format);
                $page = $this->pSMFDocumentManager->qualiosParser($page);
                $page = $this->pSMFDocumentManager->localesParser($psmf, $page, $format); 
                $template = $this->twig->createTemplate($page);
                $page = $template->render(['psmf' => $psmf]);
                //$pages[$i] = str_replace(["&quot;", "&amp;", "article"], ["\"", "&", "span"], $pages[$i]);

                $head = $this->pSMFDocumentManager->systemesParser($psmf, $header->getContenu(), $format);
                $head = $this->pSMFDocumentManager->globalesParser($head, $format);
                $head = $this->pSMFDocumentManager->qualiosParser($head);
                $head = $this->pSMFDocumentManager->localesParser($psmf, $head, $format); 

                $foot = $this->pSMFDocumentManager->systemesParser($psmf, $footer->getContenu(), $format);
                $foot = $this->pSMFDocumentManager->globalesParser($foot, $format);
                $foot = $this->pSMFDocumentManager->qualiosParser($foot);
                $foot = $this->pSMFDocumentManager->localesParser($psmf, $foot, $format);

                $contenu = $this->twig->render('PSMFManagement/PSMF/draft/pdf_section.html.twig', [
                        'header' => $head,
                        'footer' => $foot,
                        'page' => $page
                    ]
                );

                if ($request->get('debug')) return new Response($contenu);

                try {
                    $html2pdf = new Html2Pdf('P', 'A4', 'fr');
                    $html2pdf->pdf->SetDisplayMode('real');
                    $html2pdf->setTestTdInOnePage(false);
                    $html2pdf->writeHTML($contenu);
                    $html2pdf->Output($this->slugger->slug($variable->getLabel())  . '_' . (new \DateTime())->format('d-M-Y H:i:s'). '.pdf', 'D');
                } catch (Html2PdfException $e) {
                    $html2pdf->clean();
                    return new Response((new ExceptionFormatter($e))->getHtmlMessage());
                }
                break;
            case PSMF::HTML:
            default:
                $page = $this->pSMFDocumentManager->correspondanceLocale($psmf, $variable);
                $page = $this->pSMFDocumentManager->systemesParser($psmf, $page, $format);
                $page = $this->pSMFDocumentManager->globalesParser($page, $format);
                $page = $this->pSMFDocumentManager->qualiosParser($page);
                $page = $this->pSMFDocumentManager->localesParser($psmf, $page, $format); 
                $template = $this->twig->createTemplate($page);
                $page = $template->render(['psmf' => $psmf]);                                            

                $head = $this->pSMFDocumentManager->systemesParser($psmf, $header->getContenu(), $format);
                $head = $this->pSMFDocumentManager->globalesParser($head, $format);
                $head = $this->pSMFDocumentManager->qualiosParser($head);
                $head = $this->pSMFDocumentManager->localesParser($psmf, $head, $format);

                $foot = $this->pSMFDocumentManager->systemesParser($psmf, $footer->getContenu(), $format);
                $foot = $this->pSMFDocumentManager->globalesParser($foot, $format);
                $foot = $this->pSMFDocumentManager->qualiosParser($foot);
                $foot = $this->pSMFDocumentManager->localesParser($psmf, $foot, $format);

                return $this->render('PSMFManagement/PSMF/draft/html_section.html.twig', [
                    'header' => $head,
                    'footer' => $foot,
                    'page' => $page
                ]);
                break;                
        }

        return new Response();
    }

    /**
     * draft v3 with HtmlToDoc     
     * @codeCoverageIgnore
     * @Route("/draftlocale1/{psmf}/{format}", name="admin_psmf_correspondance_locale_draft1", methods={"POST"})
     * @Security("is_granted('ROLE_USER')")
     */
    public function locale(Request $request, PSMF $psmf, string $format): Response
    {          
        $contenu = $request->get('correspondance');
        $header = $this->sectionRepository->find(Section::HEADER_ID);
        $footer = $this->sectionRepository->find(Section::FOOTER_ID);
        
        switch ($format) {
            case PSMF::WORD:
                $contenu = $this->pSMFDocumentManager->systemesParser($psmf, $contenu, $format);
                $contenu = $this->pSMFDocumentManager->globalesParser($contenu, $format);
                $contenu = $this->pSMFDocumentManager->qualiosParser($contenu);
                $contenu = $this->pSMFDocumentManager->localesParser($psmf, $contenu, $format); 

                $template = $this->twig->createTemplate($contenu);
                $contenu = $template->render(['psmf' => $psmf]);

                try {
                    $htd = new HtmlToDoc();        
                    $htd->setTitle("correspondance");
                    //$htd->setHeader($header->getContenu());
                    $docfileName = $this->slugger->slug('correspondance_' . (new \DateTime())->format('d-M-Y H:i:s')).'.doc';
                    $docfilePath = $this->kernel->getProjectDir() . '/var/log/'.$docfileName;
                    $htd->setDocFilePath($docfilePath);
                    $htd->setDocFileName($docfileName);
                    $htd->createDoc($contenu, $docfileName, false); 
                    return new Response($docfileName);
                } catch (\Throwable $exception) {
                    throw new \ErrorException($e->getMessage(), $e->getCode(), \E_ERROR, $e->getFile(), $e->getLine());
                }                   
                break;
            case PSMF::PDF:
                $page = $contenu;
                $page = $this->pSMFDocumentManager->systemesParser($psmf, $page, $format);
                $page = $this->pSMFDocumentManager->globalesParser($page, $format);
                $page = $this->pSMFDocumentManager->qualiosParser($page);
                $page = $this->pSMFDocumentManager->localesParser($psmf, $page, $format); 
                $template = $this->twig->createTemplate($page);
                $page = $template->render(['psmf' => $psmf]);
                //$pages[$i] = str_replace(["&quot;", "&amp;", "article"], ["\"", "&", "span"], $pages[$i]);

                $head = $this->pSMFDocumentManager->systemesParser($psmf, $header->getContenu(), $format);
                $head = $this->pSMFDocumentManager->globalesParser($head, $format);
                $head = $this->pSMFDocumentManager->qualiosParser($head);
                $head = $this->pSMFDocumentManager->localesParser($psmf, $head, $format); 

                $foot = $this->pSMFDocumentManager->systemesParser($psmf, $footer->getContenu(), $format);
                $foot = $this->pSMFDocumentManager->globalesParser($foot, $format);
                $foot = $this->pSMFDocumentManager->qualiosParser($foot);
                $foot = $this->pSMFDocumentManager->localesParser($psmf, $foot, $format); 

                $contenu = $this->twig->render('PSMFManagement/PSMF/draft/pdf_section.html.twig', [
                        'header' => $head,
                        'footer' => $foot,
                        'page' => $page
                    ]
                );
                if ($request->get('debug')) return new Response($contenu);
                try {
                    $html2pdf = new Html2Pdf('P', 'A4', 'fr');
                    $html2pdf->pdf->SetDisplayMode('real');
                    $html2pdf->setTestTdInOnePage(false);
                    $html2pdf->writeHTML($contenu);
                    //$html2pdf->Output('locale_' . (new \DateTime())->format('d-M-Y H:i:s'). '.pdf', 'D');
                    $docfileName = $this->slugger->slug('correspondance_' . (new \DateTime())->format('d-M-Y H:i:s')).'.pdf';
                    $docfilePath = $this->kernel->getProjectDir() . '/var/log/'.$docfileName;

                    $html2pdf->output($docfilePath, 'F');
                    return new Response($docfileName);
                } catch (Html2PdfException $e) {
                    $html2pdf->clean();
                    throw new \ErrorException($e->getMessage(), $e->getCode(), \E_ERROR, $e->getFile(), $e->getLine());
                }
                break;
            case PSMF::HTML:
            default:
                $page = $contenu;
                $page = $this->pSMFDocumentManager->systemesParser($psmf, $page, $format);
                $page = $this->pSMFDocumentManager->globalesParser($page, $format);
                $page = $this->pSMFDocumentManager->qualiosParser($page);
                $page = $this->pSMFDocumentManager->localesParser($psmf, $page, $format); 
                $template = $this->twig->createTemplate($page);
                $page = $template->render(['psmf' => $psmf]);                                            

                $head = $this->pSMFDocumentManager->systemesParser($psmf, $header->getContenu(), $format);
                $head = $this->pSMFDocumentManager->globalesParser($head, $format);
                $head = $this->pSMFDocumentManager->qualiosParser($head);
                $head = $this->pSMFDocumentManager->localesParser($psmf, $head, $format); 

                $foot = $this->pSMFDocumentManager->systemesParser($psmf, $footer->getContenu(), $format);
                $foot = $this->pSMFDocumentManager->globalesParser($foot, $format);
                $foot = $this->pSMFDocumentManager->qualiosParser($foot);
                $foot = $this->pSMFDocumentManager->localesParser($psmf, $foot, $format); 

                $contenu = $this->twig->render('PSMFManagement/PSMF/draft/html.html.twig', [
                    'header' => $head,
                    'footer' => $foot,
                    'page' => $page
                ]);

                $docfileName = $this->slugger->slug('correspondance_' . (new \DateTime())->format('d-M-Y H:i:s')).'.html';
                $docfilePath = $this->kernel->getProjectDir() . '/var/log/'.$docfileName;

                $filesystem = new Filesystem();
                $filesystem->dumpFile($docfilePath, $contenu);
                return Response($docfileName);
                break;                
        }

        return new Response();
    }    
}
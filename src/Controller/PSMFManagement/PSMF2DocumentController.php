<?php

namespace App\Controller\PSMFManagement;

use App\Entity\TemplateManagement\Template;
use App\Entity\TemplateManagement\Section;
use App\Entity\TemplateManagement\Variable;
use App\Entity\PSMFManagement\PSMF;
use App\Library\HtmlToDoc;
use App\Library\HtmlToPdfConverter;
use App\Manager\PSMFManagement\PSMFDocumentManager;
use App\Repository\TemplateManagement\SectionRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Twig\Environment;
use HtmlValidator\Validator; 

/**
 * @Route("/admin/psmf")
 */
class PSMF2DocumentController extends AbstractController
{
    private $pSMFDocumentManager;
    private $sectionRepository;
    private $twig;
    private $slugger;
    private $kernel;
    private $converter;

    public function __construct(SluggerInterface $slugger, PSMFDocumentManager $pSMFDocumentManager, SectionRepository $sectionRepository, Environment $twig, KernelInterface $kernel, HtmlToPdfConverter $converter)
    {
        $this->pSMFDocumentManager = $pSMFDocumentManager;
        $this->sectionRepository = $sectionRepository;
        $this->twig = $twig;
        $this->slugger = $slugger;
        $this->kernel = $kernel;
        $this->converter = $converter;
    }

    /**
     * draft v4 with mPDF 
     * @Route("/draft/{psmf}/{format}", name="admin_psmf_draft", methods={"GET"})
     * @Security("is_granted('ROLE_USER')")
     */
    public function draft(Request $request, PSMF $psmf, string $format): Response
    {          
        error_reporting(0);
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
                $contenu = $this->pSMFDocumentManager->tableContentsParser($sections, $contenu);
                $contenu = $this->pSMFDocumentManager->localesParser($psmf, $contenu, $format); 
                $contenu = $this->pSMFDocumentManager->systemesParser($psmf, $contenu, $format);
                $contenu = $this->pSMFDocumentManager->globalesParser($contenu, $format);
                $contenu = $this->pSMFDocumentManager->qualiosParser($contenu);
                $contenu = $this->pSMFDocumentManager->logiqueParser($psmf, $contenu);

                if (null === $request->get('debug')) {
                    $template = $this->twig->createTemplate($contenu);
                    $contenu = $template->render(['psmf' => $psmf]);
                }

                $contenu = $this->twig->render('PSMFManagement/PSMF/draft/word_draft2.html.twig', [
                        'contenu' => $contenu,
                        'psmf' => $psmf
                    ]
                );
                
                if ($request->get('debug')) return new Response($contenu);

                try {
                    $htd = new HtmlToDoc();        
                    $htd->setTitle($psmf->getTitle());
                    $htd->setHeader($header->getContenu());
                    $htd->createDoc($contenu, $this->slugger->slug($psmf->getTitle()) . '_' . (new \DateTime())->format('d-M-Y H:i:s'), true); 
                } catch (\Throwable $exception) {
                    return new Response($exception->getMessage());
                }                      
                break;
            case PSMF::VALIDATOR: // html-validator for debugging
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
                $contenu = $this->pSMFDocumentManager->tableContentsParser($sections, $contenu);
                $contenu = $this->pSMFDocumentManager->localesParser($psmf, $contenu, $format); 
                $contenu = $this->pSMFDocumentManager->systemesParser($psmf, $contenu, $format);
                $contenu = $this->pSMFDocumentManager->globalesParser($contenu, $format);
                $contenu = $this->pSMFDocumentManager->qualiosParser($contenu);
                $contenu = $this->pSMFDocumentManager->logiqueParser($psmf, $contenu);

                if (null === $request->get('debug')) {
                    $template = $this->twig->createTemplate($contenu);
                    $contenu = $template->render(['psmf' => $psmf]);
                }

                $contenu = $this->twig->render('PSMFManagement/PSMF/draft/word_draft2.html.twig', [
                        'contenu' => $contenu,
                        'psmf' => $psmf
                    ]
                );
                
                if ($request->get('debug')) return new Response($contenu);         

                $validator = new Validator();
                $result = $validator->validateDocument($contenu);

                if ($result->hasErrors()) return new Response($result->toHTML());    
                else return new Response($contenu);                  
                break;                       
            case PSMF::PDF:
                foreach ($sections as $section) {
                    if ($section->getId() > 2 ) {
                        $pages[$i] = $this->pSMFDocumentManager->sectionParser($section, $section->getContenu());
                        foreach ($section->getSections() as $subSection) {
                            if ($subSection->getIsValid()) {
                                //$pages[$i] .= "<br>";
                                if ($subSection->getIsPageBreak()) { $pages[$i] .= "<pagebreak>"; } 
                                $pages[$i] .= $this->pSMFDocumentManager->sectionParser($subSection, $subSection->getContenu());
                                foreach ($subSection->getSections() as $_subSection) {
                                    if ($_subSection->getIsValid()) {
                                        //$pages[$i] .= "<br>";
                                        if ($_subSection->getIsPageBreak()) { $pages[$i] .= "<pagebreak>"; }
                                        $pages[$i] .= $this->pSMFDocumentManager->sectionParser($_subSection, $_subSection->getContenu());
                                    }
                                }           
                            }
                        }
                        $pages[$i] = $this->pSMFDocumentManager->tableContentsParser($sections, $pages[$i]);
                        $pages[$i] = $this->pSMFDocumentManager->localesParser($psmf, $pages[$i], $format);
                        $pages[$i] = $this->pSMFDocumentManager->systemesParser($psmf, $pages[$i], $format);
                        $pages[$i] = $this->pSMFDocumentManager->globalesParser($pages[$i], $format);
                        $pages[$i] = $this->pSMFDocumentManager->qualiosParser($pages[$i]);
                        $pages[$i] = $this->pSMFDocumentManager->logiqueParser($psmf, $pages[$i]);                         
                        
                        if (null === $request->get('debug')) {
                            $template = $this->twig->createTemplate($pages[$i]);
                            $pages[$i] = $template->render(['psmf' => $psmf]);
                        }
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

                $contenu = $this->twig->render('PSMFManagement/PSMF/draft/pdf2_draft.html.twig', [
                        'header' => $head,
                        'footer' => $foot,
                        'pages' => $pages,
                        'background' => $this->kernel->getProjectDir().'/public/images/pdf-draft-background.png',
                    ]
                );

                if ($request->get('debug')) return new Response($contenu);

                try {
                    $contenu = $this->converter->convertToPdf($contenu);
                    $response = new Response($contenu);
                    $disposition = $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $this->slugger->slug($psmf->getTitle()). '_' . (new \DateTime())->format('d-M-Y H:i:s'). '.pdf');
                    $response->headers->set('Content-Type', 'application/pdf');
                    $response->headers->set('Content-Disposition', $disposition);
                    return $response;
                } catch (\Throwable $exception) {
                    return new Response($exception->getMessage());
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
                        $pages[$i] = $this->pSMFDocumentManager->tableContentsParser($sections, $pages[$i]);
                        $pages[$i] = $this->pSMFDocumentManager->localesParser($psmf, $pages[$i], $format);
                        $pages[$i] = $this->pSMFDocumentManager->systemesParser($psmf, $pages[$i], $format);
                        $pages[$i] = $this->pSMFDocumentManager->globalesParser($pages[$i], $format);
                        $pages[$i] = $this->pSMFDocumentManager->qualiosParser($pages[$i]);
                        $pages[$i] = $this->pSMFDocumentManager->logiqueParser($psmf, $pages[$i]);

                        if (null === $request->get('debug')) {
                            $template = $this->twig->createTemplate($pages[$i]);
                            $pages[$i] = $template->render(['psmf' => $psmf]);                                            
                        }
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
     * draft v4 with mPDF     
     * @Route("/draftcorrespondance/{id}/{psmf}/{format}", name="admin_psmf_correspondance_draft", methods={"GET"})
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
                $contenu = $this->pSMFDocumentManager->logiqueParser($psmf, $contenu);

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
                $page = $this->pSMFDocumentManager->logiqueParser($psmf, $page); 

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

                $contenu = $this->twig->render('PSMFManagement/PSMF/draft/pdf2_section.html.twig', [
                        'header' => $head,
                        'footer' => $foot,
                        'page' => $page
                    ]
                );

                if ($request->get('debug')) return new Response($contenu);

                try {
                    $contenu = $this->converter->convertToPdf($contenu);
                    $response = new Response($contenu);
                    $disposition = $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $this->slugger->slug($variable->getLabel()). '_' . (new \DateTime())->format('d-M-Y H:i:s'). '.pdf');
                    $response->headers->set('Content-Type', 'application/pdf');
                    $response->headers->set('Content-Disposition', $disposition);
                    return $response;
                } catch (\Throwable $exception) {
                    return new Response($exception->getMessage());
                }
                break;
            case PSMF::HTML:
            default:
                $page = $this->pSMFDocumentManager->correspondanceLocale($psmf, $variable);
                $page = $this->pSMFDocumentManager->systemesParser($psmf, $page, $format);
                $page = $this->pSMFDocumentManager->globalesParser($page, $format);
                $page = $this->pSMFDocumentManager->qualiosParser($page);
                $page = $this->pSMFDocumentManager->localesParser($psmf, $page, $format); 
                $page = $this->pSMFDocumentManager->logiqueParser($psmf, $page); 

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
     * draft v4 with mPDF     
     * @Route("/draftlocale/{psmf}/{format}", name="admin_psmf_correspondance_locale_draft", methods={"POST"})
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
                $contenu = $this->pSMFDocumentManager->logiqueParser($psmf, $contenu); 

                $template = $this->twig->createTemplate($contenu);
                $contenu = $template->render(['psmf' => $psmf]);

                try {
                    $htd = new HtmlToDoc();        
                    $htd->setTitle("correspondance");
                    //$htd->setHeader($header->getContenu());
                    $docfileName = 'correspondance_' . (new \DateTime())->format('Y-m-d_H:i:s').'.doc';
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
                $page = $this->pSMFDocumentManager->logiqueParser($psmf, $page);

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

                $contenu = $this->twig->render('PSMFManagement/PSMF/draft/pdf2_section.html.twig', [
                        'header' => $head,
                        'footer' => $foot,
                        'page' => $page
                    ]
                );
                if ($request->get('debug')) return new Response($contenu);
                try {
                    $contenu = $this->converter->convertToPdf($contenu);
                    $docfileName = 'correspondance_' . (new \DateTime())->format('Y-m-d_H:i:s').'.html';
                    $docfilePath = $this->kernel->getProjectDir() . '/var/log/'.$docfileName;

                    $filesystem = new Filesystem();
                    $filesystem->dumpFile($docfilePath, $contenu);
                    return new Response($docfileName);
                } catch (\Throwable $exception) {
                    return new Response($exception->getMessage());
                }                
                break;
            case PSMF::HTML:
            default:
                $page = $contenu;
                $page = $this->pSMFDocumentManager->systemesParser($psmf, $page, $format);
                $page = $this->pSMFDocumentManager->globalesParser($page, $format);
                $page = $this->pSMFDocumentManager->qualiosParser($page);
                $page = $this->pSMFDocumentManager->localesParser($psmf, $page, $format); 
                $page = $this->pSMFDocumentManager->logiqueParser($psmf, $page);
                
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

                $docfileName = 'correspondance_' . (new \DateTime())->format('Y-m-d_H:i:s').'.html';
                $docfilePath = $this->kernel->getProjectDir() . '/var/log/'.$docfileName;

                $filesystem = new Filesystem();
                $filesystem->dumpFile($docfilePath, $contenu);
                return new Response($docfileName);
                break;                
        }

        return new Response();
    }    
}
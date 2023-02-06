<?php

namespace App\Controller\TemplateManagement;

use App\Entity\TemplateManagement\Template;
use App\Entity\TemplateManagement\Section;
use App\Entity\PSMFManagement\PSMF;
use App\Library\HtmlToDoc;
use App\Library\HtmlToPdfConverter;
use App\Manager\PSMFManagement\PSMFDocumentManager;
use App\Repository\TemplateManagement\SectionRepository;
use App\Repository\PSMFManagement\PSMFHistoryRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Twig\Environment;

/**
 * @Route("/admin/template")
 */
class TemplateController extends AbstractController
{
    public const max_history_per_page = 50;

    private $pSMFDocumentManager;
    private $sectionRepository;
    private $pSMFHistoryRepository;
    private $twig;
    private $slugger;
    private $kernel;
    private $converter;

    public function __construct(PSMFDocumentManager $pSMFDocumentManager, SectionRepository $sectionRepository, PSMFHistoryRepository $pSMFHistoryRepository, Environment $twig, SluggerInterface $slugger, KernelInterface $kernel, HtmlToPdfConverter $converter)
    {
        $this->pSMFDocumentManager = $pSMFDocumentManager;
        $this->sectionRepository = $sectionRepository;
        $this->pSMFHistoryRepository = $pSMFHistoryRepository;
        $this->twig = $twig;
        $this->slugger = $slugger;
        $this->kernel = $kernel;
        $this->converter = $converter;
    }

    /**
     * @Route("/show/{format}", name="admin_template_show", methods={"GET"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function show(Request $request, string $format): Response
    {
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
                foreach ($sections as $section) { // section parent
                    if ($section->getId() > 2) {
                        $contenu .= $this->pSMFDocumentManager->sectionParser($section, $section->getContenu());
                        foreach ($section->getSections() as $subSection) {
                            if ($subSection->getIsValid()) {
                                //$contenu .= "<br>";
                                $contenu .= $this->pSMFDocumentManager->sectionParser($subSection, $subSection->getContenu());
                                foreach ($subSection->getSections() as $_subSection) {
                                  if ($_subSection->getIsValid()) {
                                      //$contenu .= "<br/>";
                                      $contenu .= $this->pSMFDocumentManager->sectionParser($_subSection, $_subSection->getContenu());
                                  }                      
                                }                                
                            }
                        }
                    }
                }  

                $contenu = $this->pSMFDocumentManager->systemesParser(null, $contenu, $format);
                $contenu = $this->pSMFDocumentManager->globalesParser($contenu, $format, true);
                $contenu = $this->pSMFDocumentManager->qualiosParser($contenu, true);
                $contenu = $this->pSMFDocumentManager->localesParser(null, $contenu, $format); 
                $contenu = $this->pSMFDocumentManager->tableContentsParser($sections, $contenu, true);
                $contenu = $this->pSMFDocumentManager->logiqueParser(null, $contenu);

                try {
                    $htd = new HtmlToDoc();        
                    $htd->setTitle("Template");
                    //$htd->setHeader($header->getContenu());
                    $htd->createDoc($contenu, 'Template_' . (new \DateTime())->format('Y-m-d-H-i-s'), 1); 
                } catch (\Throwable $exception) {
                    return new Response($exception->getMessage());
                }                       
                break;
            case PSMF::PDF:
                foreach ($sections as $section) { 
                    if ($section->getId() > 2 ) { 
                        $pages[$i] = $this->pSMFDocumentManager->sectionParser($section, $section->getContenu()); //section parent
                        foreach ($section->getSections() as $subSection) { // section level 1
                            if ($subSection->getIsValid()) {
                                if ($subSection->getIsPageBreak()) { $pages[$i] .= "<pagebreak>"; }
                                $pages[$i] .= $this->pSMFDocumentManager->sectionParser($subSection, $subSection->getContenu());
                                foreach ($subSection->getSections() as $_subSection) { // section level 2
                                    if ($_subSection->getIsValid()) {
                                        //$pages[$i] .= "<br>";
                                        if ($_subSection->getIsPageBreak()) { $pages[$i] .= "<pagebreak>"; }
                                        $pages[$i] .= $this->pSMFDocumentManager->sectionParser($_subSection, $_subSection->
                                            getContenu());                                        
                                    }
                                }                                                                
                            } 
                        }
                        $pages[$i] = $this->pSMFDocumentManager->systemesParser(null, $pages[$i], $format);
                        $pages[$i] = $this->pSMFDocumentManager->globalesParser($pages[$i], $format, true);
                        $pages[$i] = $this->pSMFDocumentManager->qualiosParser($pages[$i], true);
                        $pages[$i] = $this->pSMFDocumentManager->localesParser(null, $pages[$i], $format);  
                        $pages[$i] = $this->pSMFDocumentManager->tableContentsParser($sections, $pages[$i], true);
                        $pages[$i] = $this->pSMFDocumentManager->logiqueParser(null, $pages[$i]);    
                        $i++;
                    }
                }  

                $head = $this->pSMFDocumentManager->systemesParser(null, $header->getContenu(), $format);
                $head = $this->pSMFDocumentManager->globalesParser($head, $format, true);
                $head = $this->pSMFDocumentManager->qualiosParser($head, true);
                $head = $this->pSMFDocumentManager->localesParser(null, $head, $format); 

                $foot = $this->pSMFDocumentManager->systemesParser(null, $footer->getContenu(), $format);
                $foot = $this->pSMFDocumentManager->globalesParser($foot, $format, true);
                $foot = $this->pSMFDocumentManager->qualiosParser($foot, true);
                $foot = $this->pSMFDocumentManager->localesParser(null, $foot, $format); 

                $contenu = $this->twig->render('PSMFManagement/PSMF/draft/pdf2.html.twig', [
                        'header' => $head,
                        'footer' => $foot,
                        'pages' => $pages
                    ]
                );

                if ($request->get('debug')) return new Response($contenu);

                try {
                    $contenu = $this->converter->convertToPdf($contenu);
                    $response = new Response($contenu);
                    $disposition = $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, 'Template_' . (new \DateTime())->format('Y-m-d_H:i:s'). '.pdf');
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
                        $pages[$i] = $this->pSMFDocumentManager->systemesParser(null, $pages[$i], $format);
                        $pages[$i] = $this->pSMFDocumentManager->globalesParser($pages[$i], $format, true);
                        $pages[$i] = $this->pSMFDocumentManager->qualiosParser($pages[$i], true);
                        $pages[$i] = $this->pSMFDocumentManager->localesParser(null, $pages[$i], $format); 
                        $pages[$i] = $this->pSMFDocumentManager->tableContentsParser($sections, $pages[$i], true);   
                        $pages[$i] = $this->pSMFDocumentManager->logiqueParser(null, $pages[$i]);               
                        $i++;
                    }
                }  
                $head = $this->pSMFDocumentManager->systemesParser(null, $header->getContenu(), $format);
                $head = $this->pSMFDocumentManager->globalesParser($head, $format, true);
                $head = $this->pSMFDocumentManager->qualiosParser($head, true);
                $head = $this->pSMFDocumentManager->localesParser(null, $head, $format);

                $foot = $this->pSMFDocumentManager->systemesParser(null, $footer->getContenu(), $format);
                $foot = $this->pSMFDocumentManager->globalesParser($foot, $format, true);
                $foot = $this->pSMFDocumentManager->qualiosParser($foot, true);
                $foot = $this->pSMFDocumentManager->localesParser(null, $foot, $format);

                return $this->render('PSMFManagement/PSMF/draft/html.html.twig', [
                    'header' => $head,
                    'footer' => $foot,
                    'pages' => $pages
                ]);
                break; 
        } 

        return new Response();     
    }

    /**
     * @Route("/section/{id}/{format}", name="admin_template_section", methods={"GET"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function section(Request $request, Section $section, string $format): Response
    {
        $header = $this->sectionRepository->find(Section::HEADER_ID);
        $footer = $this->sectionRepository->find(Section::FOOTER_ID);

        switch ($format) {
            case PSMF::WORD:
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
                $contenu = $this->pSMFDocumentManager->systemesParser(null, $contenu, $format);
                $contenu = $this->pSMFDocumentManager->globalesParser($contenu, $format, false);
                $contenu = $this->pSMFDocumentManager->qualiosParser($contenu, false);
                $contenu = $this->pSMFDocumentManager->localesParser(null, $contenu, $format); 
                $contenu = $this->pSMFDocumentManager->logiqueParser(null, $contenu);

                $title = $this->slugger->slug($section->getTitle());

                try {
                    $htd = new HtmlToDoc();        
                    $htd->setTitle($title);
                    //$htd->setHeader($header->getContenu());
                    $htd->createDoc($contenu, $title . '_' . (new \DateTime())->format('Y-m-d-H-i-s'), 1); 
                } catch (\Throwable $exception) {
                    return new Response($exception->getMessage());
                }                       
                break;
            case PSMF::PDF:
                $page = $this->pSMFDocumentManager->sectionParser($section, $section->getContenu());
                foreach ($section->getSections() as $subSection) {
                    if ($subSection->getIsValid()) {
                        //$pages[$i] .= "<br>";
                        $page .= $this->pSMFDocumentManager->sectionParser($subSection, $subSection->getContenu());
                        foreach ($subSection->getSections() as $_subSection) {
                            if ($_subSection->getIsValid()) {
                                //$pages[$i] .= "<br>";
                                $page .= $this->pSMFDocumentManager->sectionParser($_subSection, $_subSection->getContenu());
                            }
                        }                                
                    }
                }                
                $page = $this->pSMFDocumentManager->systemesParser(null, $page, $format);
                $page = $this->pSMFDocumentManager->globalesParser($page, $format, false);
                $page = $this->pSMFDocumentManager->qualiosParser($page, false);  
                $page = $this->pSMFDocumentManager->localesParser(null, $page, $format);  
                //$pages[$i] = $this->pSMFDocumentManager->tableContentsParser($sections, $pages[$i], false);
                $page = $this->pSMFDocumentManager->logiqueParser(null, $page);    

                $head = $this->pSMFDocumentManager->systemesParser(null, $header->getContenu(), $format);
                $head = $this->pSMFDocumentManager->globalesParser($head, $format, false);
                $head = $this->pSMFDocumentManager->qualiosParser($head, false);
                $head = $this->pSMFDocumentManager->localesParser(null, $head, $format);

                $foot = $this->pSMFDocumentManager->systemesParser(null, $footer->getContenu(), $format);
                $foot = $this->pSMFDocumentManager->globalesParser($foot, $format, false);
                $foot = $this->pSMFDocumentManager->qualiosParser($foot, false);
                $foot = $this->pSMFDocumentManager->localesParser(null, $foot, $format);

                $contenu = $this->twig->render('PSMFManagement/PSMF/draft/pdf2_section.html.twig', [
                        'header' => $head,
                        'footer' => $foot,
                        'page' => $page
                    ]
                );
                
                if ($request->get('debug')) return new Response($contenu);

                $title = $this->slugger->slug($section->getTitle());

                try {
                    $contenu = $this->converter->convertToPdf($contenu);
                    $response = new Response($contenu);
                    $disposition = $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $title . '_' . (new \DateTime())->format('d-M-Y H:i:s'). '.pdf');
                    $response->headers->set('Content-Type', 'application/pdf');
                    $response->headers->set('Content-Disposition', $disposition);
                    return $response;
                } catch (\Throwable $exception) {
                    return new Response($exception->getMessage());
                }                
                break;               
            case PSMF::HTML:
            default:
                $page = $this->pSMFDocumentManager->sectionParser($section, $section->getContenu());
                foreach ($section->getSections() as $subSection) {
                    if ($subSection->getIsValid()) {
                        //$pages[$i] .= "<br>";
                        $page .= $this->pSMFDocumentManager->sectionParser($subSection, $subSection->getContenu());
                        foreach ($subSection->getSections() as $_subSection) {
                            if ($_subSection->getIsValid()) {
                                //$pages[$i] .= "<br>";
                                $page .= $this->pSMFDocumentManager->sectionParser($_subSection, $_subSection->getContenu());
                            }
                        }                                
                    }
                }                  
                $page = $this->pSMFDocumentManager->systemesParser(null, $page, $format);
                $page = $this->pSMFDocumentManager->globalesParser($page, $format, false);
                $page = $this->pSMFDocumentManager->qualiosParser($page, false);
                $page = $this->pSMFDocumentManager->localesParser(null, $page, $format); 
                $page = $this->pSMFDocumentManager->tableContentsParser($sections, $page, false);   
                $page = $this->pSMFDocumentManager->logiqueParser(null, $page);               

                $head = $this->pSMFDocumentManager->systemesParser(null, $header->getContenu(), $format);
                $head = $this->pSMFDocumentManager->globalesParser($head, $format, false);
                $head = $this->pSMFDocumentManager->qualiosParser($head, false);
                $head = $this->pSMFDocumentManager->localesParser(null, $head, $format);

                $foot = $this->pSMFDocumentManager->systemesParser(null, $footer->getContenu(), $format);
                $foot = $this->pSMFDocumentManager->globalesParser($foot, $format, false);
                $foot = $this->pSMFDocumentManager->qualiosParser($foot, false);
                $foot = $this->pSMFDocumentManager->localesParser(null, $foot, $format);

                return $this->render('PSMFManagement/PSMF/draft/html_section.html.twig', [
                    'header' => $head,
                    'footer' => $foot,
                    'pages' => $pages
                ]);
                break; 
        } 

        return new Response();     
    }

    /**
     * @Route("/contenu/{format}", name="admin_template_section_contenu", methods={"POST"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function contenu(Request $request, string $format): Response
    {
        $contenu = $request->get('correspondance');
        $title = $request->get('title');
        $contenu = str_replace('SYS_PART_TITRE', $title, $contenu);
        $header = $this->sectionRepository->find(Section::HEADER_ID);
        $footer = $this->sectionRepository->find(Section::FOOTER_ID);

        switch ($format) {
            case PSMF::WORD:
                $contenu = $this->pSMFDocumentManager->systemesParser(null, $contenu, $format);
                $contenu = $this->pSMFDocumentManager->globalesParser($contenu, $format, false);
                $contenu = $this->pSMFDocumentManager->qualiosParser($contenu, false);
                $contenu = $this->pSMFDocumentManager->localesParser(null, $contenu, $format); 
                $contenu = $this->pSMFDocumentManager->logiqueParser(null, $contenu);

                try {
                    $htd = new HtmlToDoc();        
                    $htd->setTitle($title);
                    //$htd->setHeader($header->getContenu());
                    $docfileName = $this->slugger->slug('contenu_' . (new \DateTime())->format('d-M-Y H:i:s')).'.doc';
                    $docfilePath = $this->kernel->getProjectDir() . '/var/log/'.$docfileName;
                    $htd->setDocFilePath($docfilePath);
                    $htd->setDocFileName($docfileName);
                    $htd->createDoc($contenu, $docfileName, false); 
                    return new Response($docfileName);
                } catch (\Throwable $e) {
                    throw new \ErrorException($e->getMessage(), $e->getCode(), \E_ERROR, $e->getFile(), $e->getLine());
                }                      
                break;
            case PSMF::PDF:
                $page = $contenu;
                $page = $this->pSMFDocumentManager->systemesParser(null, $page, $format);
                $page = $this->pSMFDocumentManager->globalesParser($page, $format, false);
                $page = $this->pSMFDocumentManager->qualiosParser($page, false);
                $page = $this->pSMFDocumentManager->localesParser(null, $page, $format);  
                //$pages[$i] = $this->pSMFDocumentManager->tableContentsParser($sections, $pages[$i], false);
                $page = $this->pSMFDocumentManager->logiqueParser(null, $page);      

                $head = $this->pSMFDocumentManager->systemesParser(null, $header->getContenu(), $format);
                $head = $this->pSMFDocumentManager->globalesParser($head, $format, false);
                $head = $this->pSMFDocumentManager->qualiosParser($head, false);
                $head = $this->pSMFDocumentManager->localesParser(null, $head, $format);

                $foot = $this->pSMFDocumentManager->systemesParser(null, $footer->getContenu(), $format);
                $foot = $this->pSMFDocumentManager->globalesParser($foot, $format, false);
                $foot = $this->pSMFDocumentManager->qualiosParser($foot, false);
                $foot = $this->pSMFDocumentManager->localesParser(null, $foot, $format);

                $contenu = $this->twig->render('PSMFManagement/PSMF/draft/pdf2_section.html.twig', [
                        'header' => $head,
                        'footer' => $foot,
                        'page' => $page
                    ]
                );

                if ($request->get('debug')) return new Response($contenu);

                try {
                    $contenu = $this->converter->convertToPdf($contenu);

                    $docfileName = $this->slugger->slug('contenu_' . (new \DateTime())->format('d-M-Y H:i:s')).'.html';
                    $docfilePath = $this->kernel->getProjectDir() . '/var/log/'.$docfileName;

                    $filesystem = new Filesystem();
                    $filesystem->dumpFile($docfilePath, $contenu);
                    return new Response($docfileName);

                } catch (\Throwable $e) {
                    throw new \ErrorException($e->getMessage(), $e->getCode(), \E_ERROR, $e->getFile(), $e->getLine());
                }                
                break;               
            case PSMF::HTML:
            default:
                $page = $contenu;
                $page = $this->pSMFDocumentManager->systemesParser(null, $page, $format);
                $page = $this->pSMFDocumentManager->globalesParser($page, $format, false);
                $page = $this->pSMFDocumentManager->qualiosParser($page, false);
                $page = $this->pSMFDocumentManager->localesParser(null, $page, $format); 
                $page = $this->pSMFDocumentManager->tableContentsParser($sections, $page, false);   
                $page = $this->pSMFDocumentManager->logiqueParser(null, $page);               

                $head = $this->pSMFDocumentManager->systemesParser(null, $header->getContenu(), $format);
                $head = $this->pSMFDocumentManager->globalesParser($head, $format, false);
                $head = $this->pSMFDocumentManager->qualiosParser($head, false);
                $head = $this->pSMFDocumentManager->localesParser(null, $head, $format);

                $foot = $this->pSMFDocumentManager->systemesParser(null, $footer->getContenu(), $format);
                $foot = $this->pSMFDocumentManager->globalesParser($foot, $format, false);
                $foot = $this->pSMFDocumentManager->qualiosParser($foot, false);
                $foot = $this->pSMFDocumentManager->localesParser(null, $foot, $format);

                $docfileName = $this->slugger->slug('contenu_' . (new \DateTime())->format('d-M-Y H:i:s')).'.html';
                $docfilePath = $this->kernel->getProjectDir() . '/var/log/'.$docfileName;

                $filesystem = new Filesystem();
                $filesystem->dumpFile($docfilePath, $contenu);
                return new Response($docfileName);
                break; 
        } 

        return new Response();     
    }    

    /**
     * @Route("/userHelp/{format}", name="admin_template_variable_userHelp", methods={"POST"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function userHelp(Request $request, string $format): Response
    {
        $contenu = $request->get('correspondance');
        $header = $this->sectionRepository->find(Section::HEADER_ID);
        $footer = $this->sectionRepository->find(Section::FOOTER_ID);

        switch ($format) {
            case PSMF::WORD:
                $contenu = $this->pSMFDocumentManager->systemesParser(null, $contenu, $format);
                $contenu = $this->pSMFDocumentManager->globalesParser($contenu, $format, false);
                $contenu = $this->pSMFDocumentManager->qualiosParser($contenu, false);
                $contenu = $this->pSMFDocumentManager->localesParser(null, $contenu, $format); 
                $contenu = $this->pSMFDocumentManager->logiqueParser(null, $contenu);

                try {
                    $htd = new HtmlToDoc();        
                    $htd->setTitle('userHelp');
                    //$htd->setHeader($header->getContenu());
                    $docfileName = $this->slugger->slug('userHelp_' . (new \DateTime())->format('d-M-Y H:i:s')).'.doc';
                    $docfilePath = $this->kernel->getProjectDir() . '/var/log/'.$docfileName;
                    $htd->setDocFilePath($docfilePath);
                    $htd->setDocFileName($docfileName);
                    $htd->createDoc($contenu, $docfileName, false); 
                    return new Response($docfileName);
                } catch (\Throwable $e) {
                    throw new \ErrorException($e->getMessage(), $e->getCode(), \E_ERROR, $e->getFile(), $e->getLine());
                }                      
                break;
            case PSMF::PDF:
                $page = $contenu;
                $page = $this->pSMFDocumentManager->systemesParser(null, $page, $format);
                $page = $this->pSMFDocumentManager->globalesParser($page, $format, false);
                $page = $this->pSMFDocumentManager->qualiosParser($page, false);
                $page = $this->pSMFDocumentManager->localesParser(null, $page, $format);  
                //$pages[$i] = $this->pSMFDocumentManager->tableContentsParser($sections, $pages[$i], false);
                $page = $this->pSMFDocumentManager->logiqueParser(null, $page);                      
                $page = str_replace(["&quot;", "&amp;"], ["\"", "&"], $page);

                $head = $this->pSMFDocumentManager->systemesParser(null, $header->getContenu(), $format);
                $head = $this->pSMFDocumentManager->globalesParser($head, $format, false);
                $head = $this->pSMFDocumentManager->qualiosParser($head, false);
                $head = $this->pSMFDocumentManager->localesParser(null, $head, $format);  

                $foot = $this->pSMFDocumentManager->systemesParser(null, $footer->getContenu(), $format);
                $foot = $this->pSMFDocumentManager->globalesParser($foot, $format, false);
                $foot = $this->pSMFDocumentManager->qualiosParser($foot, false);
                $foot = $this->pSMFDocumentManager->localesParser(null, $foot, $format);

                $contenu = $this->twig->render('PSMFManagement/PSMF/draft/pdf2_section.html.twig', [
                        'header' => $head,
                        'footer' => $foot,
                        'page' => $page
                    ]
                );

                if ($request->get('debug')) return new Response($contenu);
                
                try {
                    $contenu = $this->converter->convertToPdf($contenu);

                    $docfileName = $this->slugger->slug('userHelp_' . (new \DateTime())->format('d-M-Y H:i:s')).'.html';
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
                $page = $this->pSMFDocumentManager->systemesParser(null, $page, $format);
                $page = $this->pSMFDocumentManager->globalesParser($page, $format, false);
                $page = $this->pSMFDocumentManager->qualiosParser($page, false);
                $page = $this->pSMFDocumentManager->localesParser(null, $page, $format); 
                $page = $this->pSMFDocumentManager->tableContentsParser($sections, $page, false);   
                $page = $this->pSMFDocumentManager->logiqueParser(null, $page);               

                $head = $this->pSMFDocumentManager->systemesParser(null, $header->getContenu(), $format);
                $head = $this->pSMFDocumentManager->globalesParser($head, $format, false);
                $head = $this->pSMFDocumentManager->qualiosParser($head, false);
                $head = $this->pSMFDocumentManager->localesParser(null, $head, $format);

                $foot = $this->pSMFDocumentManager->systemesParser(null, $footer->getContenu(), $format);
                $foot = $this->pSMFDocumentManager->globalesParser($foot, $format, false);
                $foot = $this->pSMFDocumentManager->qualiosParser($foot, false);
                $foot = $this->pSMFDocumentManager->localesParser(null, $foot, $format);
                
                $docfileName = $this->slugger->slug('userHelp_' . (new \DateTime())->format('d-M-Y H:i:s')).'.html';
                $docfilePath = $this->kernel->getProjectDir() . '/var/log/'.$docfileName;

                $filesystem = new Filesystem();
                $filesystem->dumpFile($docfilePath, $contenu);
                return new Response($docfileName);
                break; 
        } 

        return new Response();     
    }

    /**   
     * @Route("/history", name="admin_template_history", defaults={"page": "1"}, methods={"GET"})
     * @Route("/history/{page}", requirements={"page"="\d+"}, name="admin_template_history_paginated")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function history(int $page): Response
    {        
        $total = $this->pSMFHistoryRepository->getTotalBySectionModification();

        $pagination = [
            'page' => $page,
            'route' => 'admin_template_history_paginated',
            'pages_count' => ceil($total / self::max_history_per_page),
            'route_params' => [],
        ];

        $pSMFSectionHistories = $this->pSMFHistoryRepository->findBySectionModification($page, self::max_history_per_page);      
          
        return $this->render('TemplateManagement/Section/history.html.twig', [
            'pSMFSectionHistories' => $pSMFSectionHistories,
            'pagination' => $pagination,            
        ]);        
    } 

    /**   
     * @Route("/history/download", name="admin_template_history_download", methods={"GET"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function download(): Response
    {        

        $content = $this->twig->render('TemplateManagement/Section/history_excel.html.twig', [
            'pSMFSectionHistories' =>  $this->pSMFHistoryRepository->findAllBySectionModification()]);

        return new Response($content, 200, [
            'Content-Type' => 'application/force-download;charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="template_history_' . (new \Datetime())->format('d-M-Y H:i:s') . '.xls"'
        ]);      
    } 
}

<?php

namespace App\Controller\TemplateManagement;

use App\Entity\TemplateManagement\Template;
use App\Entity\TemplateManagement\Section;
use App\Form\TemplateManagement\SectionType;
use App\Form\TemplateManagement\SectionEditType;
use App\Form\TemplateManagement\SectionShowType;
use App\Repository\TemplateManagement\SectionRepository;
use App\Repository\TemplateManagement\TemplateRepository;
use App\Repository\TemplateManagement\VariableRepository;
use App\Repository\TemplateManagement\ClasseRepository;
use App\Message\TemplateManagement\SectionUpdated;
use App\Serializer\TemplateManagement\SectionSerializer;
use Doctrine\DBAL\Exception as DBALException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/section")
 */
class SectionController extends AbstractController
{
    private $messageBus;
    private $sectionSerializer;

    public function __construct(SectionSerializer $sectionSerializer, MessageBusInterface $messageBus)
    {
        $this->sectionSerializer = $sectionSerializer;
        $this->messageBus = $messageBus;
    }

    /**
     * @Route("/index", name="admin_section_index", methods={"GET"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function index(SectionRepository $sectionRepository): Response
    {
        return $this->render('TemplateManagement/Section/index.html.twig', [
            'header' => $sectionRepository->find(Section::HEADER_ID),
            'footer' => $sectionRepository->find(Section::FOOTER_ID),
            'sections' => $sectionRepository->findBy([
                    'template' => Template::TEMPLATE_ID,
                    'parent' => NULL,
                    'isDeleted' => false
                ],[
                    'position' => 'ASC',
                ]),
        ]);
    }

    /**
     * Subtemplate embed controller
     */     
    public function variables(VariableRepository $variableRepository): Response
    {
        return $this->render('TemplateManagement/Section/variables.html.twig', [
            'systemes' => $variableRepository->findEquivalencesSystemes(),
            'globales' => $variableRepository->findEquivalencesGlobales(),
            'locales' => $variableRepository->findEquivalencesLocales(),
        ]);        
    }

    /**
     * Subtemplate embed controller
     */     
    public function classes(ClasseRepository $classeRepository, ?Section $section = null): Response
    {
        // find classes by scope: system, global, local variable by section
        return $this->render('TemplateManagement/Section/classes.html.twig', [
            //'classes' => $classeRepository->findBy(['isDeleted'=>false], ['title'=>'ASC']),
            'classes' => $classeRepository->findBySection($section),
        ]);        
    }

    /**
     * @Route("/new/{parent}", name="admin_section_new", defaults={"parent": null}, methods={"GET","POST"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function new(Request $request, Section $parent=null, ClasseRepository $classeRepository, TemplateRepository $templateRepository, TranslatorInterface $translator): Response
    {
        $template = $templateRepository->find(Template::TEMPLATE_ID);
        $section = new Section();
        $section->setTemplate($template);
        if ($parent) {
            $section->setParent($parent);
            $section->setPosition($parent->getLastPosition());
        } else {
            $section->setIsPageBreak(true);
            $section->setPosition($template->getLastPosition());
        }     
        $section->setIsMajeur(false);
        $form = $this->createForm(SectionType::class, $section);
        $form->handleRequest($request);
        $old = [];
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            try {
                $section->setCreateUser($this->getUser());
                $section->setUpdateUser($this->getUser());
                $section->setCreateDate(new \Datetime());
                $section->setUpdateDate(new \Datetime());   

                $entityManager->persist($section);
                $entityManager->flush();

                $this->messageBus->dispatch(new SectionUpdated('add', $old, $section));

                $this->addFlash('success', $translator->trans('section.flash.created'));

                return $this->redirectToRoute('admin_section_index');
            } catch (DBALException $exception) {
                $this->addFlash('error', $exception->getMessage());
            } catch (\Throwable $exception) {
                $this->addFlash('error', $exception->getMessage());
            }  
        }

        return $this->render('TemplateManagement/Section/new.html.twig', [
            'parent' => $parent,
            'section' => $section,
            'form' => $form->createView(),
            'classesGlobale' => $classeRepository->findByGlobale(),
            'classesSysteme' => $classeRepository->findBySysteme(),
            'classes' => null,
        ]);
    }

    /**
     * @Route("/show/{id}", name="admin_section_show", methods={"GET"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function show(Request $request, Section $section): Response
    {
        $form = $this->createForm(SectionShowType::class, $section);

        return $this->render('TemplateManagement/Section/show.html.twig', [
            'section' => $section,
            'form' => $form->createView(),
            'action' => $request->get('action'),
        ]);
    }

    /**
     * @Route("/edit/{id}", name="admin_section_edit", methods={"GET","POST"})
     * @IsGranted("SECTION_EDIT", subject="section", message="Vous ne pouvez pas modifier cette section.")
     */
    public function edit(Request $request, Section $section, ClasseRepository $classeRepository, TranslatorInterface $translator): Response
    {
        $old = $this->sectionSerializer->serialize($section);

        $section->setIsMajeur(false); // set section is majeur false
        $form = $this->createForm(SectionEditType::class, $section);
        $form->handleRequest($request);        

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $section->setUpdateUser($this->getUser());
            $section->setUpdateDate(new \Datetime());

            try {
                $em->persist($section);
                $em->flush();

                if ($section->getIsMajeur()) {
                   $this->messageBus->dispatch(new SectionUpdated('edit', $old, $section)); 
                } 
                
                $this->addFlash('success', $translator->trans('section.flash.updated'));

                return $this->redirect($this->generateUrl('admin_section_show', [
                    'id'=>$section->getId()
                ]));
            } catch (DBALException $exception) {
                $this->addFlash('error', $exception->getMessage());
            }
        }

        return $this->render('TemplateManagement/Section/edit.html.twig', [
            'section' => $section,
            'form' => $form->createView(),
            'classesGlobale' => $classeRepository->findByGlobale(),
            'classesSysteme' => $classeRepository->findBySysteme(),
            'classes' => $classeRepository->findBySection($section),
        ]);
    }

    /**
     * @Route("/sidebar/{section}", name="admin_section_sidebar", defaults={"section": NULL}, methods={"GET"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function sidebar(Section $section=NULL, SectionRepository $sectionRepository): Response
    {
        return $this->render('TemplateManagement/Section/left_sidebar.html.twig', [
            'header' => $sectionRepository->find(Section::HEADER_ID),
            'footer' => $sectionRepository->find(Section::FOOTER_ID),
            'section' => $section,
            'sections' => $sectionRepository->findBy([
                    'template' => Template::TEMPLATE_ID,
                    'parent' => NULL,
                    'isDeleted' => false
                ],[
                    'position' => 'ASC',
                ]),
        ]);
    }
    
    /**
     * Desactive une section
     * @Route("/disable/{id}", name="admin_section_disable", methods={"POST"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function disable(Request $request, Section $section, TranslatorInterface $translator)
    {
        $old = $this->sectionSerializer->serialize($section);

        $form = $this->createForm(SectionShowType::class, $section);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            try {
                $section->setUpdateUser($this->getUser());
                $section->setUpdateDate(new \Datetime());      
                if ($section->getIsValid()) {
                    $section->setIsValid(false);
                } else {
                    $section->setIsValid(true);
                }                             
                $em->persist($section);
                $em->flush();
                if ($section->getIsValid()) {
                    $this->messageBus->dispatch(new SectionUpdated('enable', $old, $section));
                    $this->addFlash('success', $translator->trans('section.flash.enable'));
                } else {
                    $this->messageBus->dispatch(new SectionUpdated('disable', $old, $section));
                    $this->addFlash('success', $translator->trans('section.flash.disable'));
                }  
                return $this->redirectToRoute('admin_section_index');

            } catch (DBALException $exception) {
                $this->addFlash('error', $exception->getMessage());
            }
        }

        if ($section->getIsValid()) {
            return $this->redirectToRoute('admin_section_show', ['id'=>$section->getId(), 'action'=>'disable']);
        } else {
            return $this->redirectToRoute('admin_section_show', ['id'=>$section->getId(), 'action'=>'enable']);
        }  
        
    }
    /**
     * @Route("/delete/{id}", name="admin_section_delete", methods={"POST"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function delete(Request $request, Section $section, TranslatorInterface $translator): Response
    {
        $old = $this->sectionSerializer->serialize($section);

        $form = $this->createForm(SectionShowType::class, $section);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            try {
                $section->setUpdateUser($this->getUser());
                $section->setUpdateDate(new \Datetime());      
                $section->setIsValid(false);
                $section->setIsDeleted(true);                           
                $em->persist($section);
                $em->flush();

                $this->messageBus->dispatch(new SectionUpdated('delete', $old, $section));
                $this->addFlash('success', $translator->trans('section.flash.deleted'));
                return $this->redirectToRoute('admin_section_index');

            } catch (DBALException $exception) {
                $this->addFlash('error', $exception->getMessage());
            }
        }

        return $this->redirectToRoute('admin_section_show', ['id'=>$section->getId(), 'action'=>'delete']);
    }
}

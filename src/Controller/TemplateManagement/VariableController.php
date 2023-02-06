<?php

namespace App\Controller\TemplateManagement;

use App\Entity\TemplateManagement\Variable;
use App\Form\TemplateManagement\VariableShowType;
use App\Form\TemplateManagement\VariableType;
use App\Form\TemplateManagement\VariableEditType;
use App\Repository\TemplateManagement\VariableRepository;
use App\Repository\TemplateManagement\ClasseRepository;
use App\Message\TemplateManagement\VariableUpdated;
use App\Serializer\TemplateManagement\VariableSerializer;
use Doctrine\DBAL\Exception as DBALException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

/**
 * @Route("/admin/variable")
 */
class VariableController extends AbstractController
{
    private $messageBus;
    private $variableSerializer;

    public function __construct(VariableSerializer $variableSerializer, MessageBusInterface $messageBus)
    {
        $this->variableSerializer = $variableSerializer;
        $this->messageBus = $messageBus;
    }

    /**
     * @Route("/index", name="admin_variable_index", methods={"GET"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function index(VariableRepository $variableRepository): Response
    {
        return $this->render('TemplateManagement/Variable/index.html.twig', [
            'variables' => $variableRepository->findBy(['isDeleted'=>false], ['label'=>'DESC']),
        ]);
    }

    /**
     * @Route("/new", name="admin_variable_new", methods={"GET","POST"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function new(Request $request, ClasseRepository $classeRepository, TranslatorInterface $translator): Response
    {
        $variable = new Variable();
        $form = $this->createForm(VariableType::class, $variable);
        $form->handleRequest($request);

        $old = [];
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            try {
                $variable->setCreateUser($this->getUser());
                $variable->setUpdateUser($this->getUser());

                $entityManager->persist($variable);
                $entityManager->flush();

                //$this->messageBus->dispatch(new VariableUpdated('add', $old, $variable));
                $this->addFlash('success', $translator->trans('variable.flash.created'));

                return $this->redirectToRoute('admin_variable_show', [
                    'id'=>$variable->getId()
                ]);
            } catch (DBALException $exception) {
                $this->addFlash('error', $exception->getMessage());
            } catch (\Throwable $exception) {
                $this->addFlash('error', $exception->getMessage());
            }                
        }

        return $this->render('TemplateManagement/Variable/new.html.twig', [
            'variable' => $variable,
            'form' => $form->createView(),
            'classesGlobale' => $classeRepository->findByGlobale(),
            'classesSysteme' => $classeRepository->findBySysteme(),
            'classes' => null
        ]);
    }

    /**
     * @Route("/show/{id}", name="admin_variable_show", methods={"GET"})
     * @Security("is_granted('ROLE_ADMIN')")     
     */
    public function show(Request $request, Variable $variable): Response
    {
        $form = $this->createForm(VariableShowType::class, $variable);
        
        return $this->render('TemplateManagement/Variable/show.html.twig', [
            'variable' => $variable,
            'form' => $form->createView(),
            'action' => $request->get('action'),            
        ]);
    }

    /**
     * @Route("/edit/{id}", name="admin_variable_edit", methods={"GET","POST"})
     * @Security("is_granted('ROLE_ADMIN')") 
     */
    public function edit(Request $request, Variable $variable, ClasseRepository $classeRepository, TranslatorInterface $translator): Response
    {
        $old = $this->variableSerializer->serialize($variable);
        
        $form = $this->createForm(VariableEditType::class, $variable);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $variable->setUpdateUser($this->getUser());

            try {
                $em->persist($variable);
                $em->flush();

                //$this->messageBus->dispatch(new VariableUpdated('edit', $old, $variable));
                $this->addFlash('success', $translator->trans('variable.flash.updated'));

                return $this->redirectToRoute('admin_variable_show', [
                    'id'=>$variable->getId()
                ]);
            } catch (DBALException $exception) {
                $this->addFlash('error', $exception->getMessage());
            } catch (\Throwable $exception) {
                $this->addFlash('error', $exception->getMessage());
            } 
        }

        return $this->render('TemplateManagement/Variable/edit.html.twig', [
            'variable' => $variable,
            'form' => $form->createView(),
            'classesGlobale' => $classeRepository->findByGlobale(),
            'classesSysteme' => $classeRepository->findBySysteme(),
            'classes' => null
        ]);
    }

    /**
     * Desactive une variable
     * @Route("/disableOrEnable/{id}", name="admin_variable_disable_or_enable", methods={"POST"})
     * @IsGranted("VARIABLE_DISABLE", subject="variable", message="You cannot disable this variable.")
     */
    public function disableOrEnable(Request $request, Variable $variable, TranslatorInterface $translator)
    {
        $old = $this->variableSerializer->serialize($variable);

        $form = $this->createForm(VariableShowType::class, $variable);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            try {
                $variable->setUpdateUser($this->getUser());
                $variable->setUpdateDate(new \Datetime());      
                if ($variable->getIsValid()) {
                    $variable->setIsValid(false);
                } else {
                    $variable->setIsValid(true);
                }                             
                $em->persist($variable);
                $em->flush();
                if ($variable->getIsValid()) {
                    $this->messageBus->dispatch(new VariableUpdated('enable', $old, $variable));
                    $this->addFlash('success', $translator->trans('variable.flash.enabled'));
                } else {
                    $this->messageBus->dispatch(new VariableUpdated('disable', $old, $variable));
                    $this->addFlash('success', $translator->trans('variable.flash.disabled'));
                }  
                return $this->redirectToRoute('admin_variable_index');

            } catch (DBALException $exception) {
                $this->addFlash('error', $exception->getMessage());
            }
        }

        if ($variable->getIsValid()) {
            return $this->redirectToRoute('admin_variable_show', ['id'=>$variable->getId(), 'action'=>'disable']);
        } else {
            return $this->redirectToRoute('admin_variable_show', ['id'=>$variable->getId(), 'action'=>'enable']);
        }  
    }

    /**
     * @Route("/delete/{id}", name="admin_variable_delete", methods={"POST"})
     * @IsGranted("VARIABLE_DELETE", subject="variable", message="You cannot delete this variable.")
     */
    public function delete(Request $request, Variable $variable, TranslatorInterface $translator): Response
    {
        $old = $this->variableSerializer->serialize($variable);

        $form = $this->createForm(VariableShowType::class, $variable);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            try {
                $variable->setUpdateUser($this->getUser());     
                $variable->setIsValid(false);
                $variable->setIsDeleted(true);                           
                $em->persist($variable);
                $em->flush();

                $this->messageBus->dispatch(new VariableUpdated('delete', $old, $variable));
                $this->addFlash('success', $translator->trans('variable.flash.deleted'));
                return $this->redirectToRoute('admin_variable_index');

            } catch (DBALException $exception) {
                $this->addFlash('error', $exception->getMessage());
            }
        }

        return $this->redirectToRoute('admin_variable_show', ['id'=>$variable->getId(), 'action'=>'delete']);
    }

    /**
     * @Route("/download", name="admin_variable_download", methods={"GET"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function download(VariableRepository $variableRepository, Environment $twig): Response
    {
        $content = $twig->render('TemplateManagement/Variable/download.html.twig', [
            'variables' => $variableRepository->findBy(['isDeleted'=>false], ['label'=>'DESC'])
        ]);

        return new Response($content, 200, [
            'Content-Type' => 'application/force-download;charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="variables_' . (new \Datetime())->format('d-M-Y H:i:s') . '.xls"'
        ]);        

    }

}

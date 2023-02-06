<?php

namespace App\Controller\TemplateManagement;

use App\Entity\TemplateManagement\Classe;
use App\Form\TemplateManagement\ClasseType;
use App\Form\TemplateManagement\ClasseShowType;
use App\Repository\TemplateManagement\ClasseRepository;
use Doctrine\DBAL\Exception as DBALException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

/**
 * @Route("/admin/classe")
 */
class ClasseController extends AbstractController
{
    /**
     * @Route("/index", name="admin_classe_index", methods={"GET"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function index(ClasseRepository $classeRepository): Response
    {
        return $this->render('TemplateManagement/Classe/index.html.twig', [
            'classes' => $classeRepository->findBy(['isDeleted'=>false], ['title'=>'DESC']),
        ]);
    }

    /**
     * @Route("/new", name="admin_classe_new", methods={"GET","POST"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function new(Request $request, TranslatorInterface $translator): Response
    {
        $classe = new Classe();
        $form = $this->createForm(ClasseType::class, $classe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            try {

                $classe->setCreateUser($this->getUser());
                $classe->setUpdateUser($this->getUser());
                
                foreach ($classe->getVariables() as $variable) {
                    $variable->addClasse($classe);
                    $entityManager->persist($variable);
                }    

                $entityManager->persist($classe);
                $entityManager->flush();

                $this->addFlash('success', $translator->trans('classe.flash.created'));

                return $this->redirectToRoute('admin_classe_show', [
                    'id'=>$classe->getId()
                ]);
            } catch (DBALException $exception) {
                $this->addFlash('error', $exception->getMessage());
            } catch (\Throwable $exception) {
                $this->addFlash('error', $exception->getMessage());
            } 
        }

        return $this->render('TemplateManagement/Classe/new.html.twig', [
            'classe' => $classe,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/show/{id}", name="admin_classe_show", methods={"GET"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function show(Request $request, Classe $classe): Response
    {
        $form = $this->createForm(ClasseShowType::class, $classe);

        return $this->render('TemplateManagement/Classe/show.html.twig', [
            'classe' => $classe,
            'form' => $form->createView(),
            'action' => $request->get('action'),
        ]);
    }

    /**
     * @Route("/edit/{id}", name="admin_classe_edit", methods={"GET","POST"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function edit(Request $request, Classe $classe, TranslatorInterface $translator): Response
    {
        $em = $this->getDoctrine()->getManager();

        $form = $this->createForm(ClasseType::class, $classe);
        $form->handleRequest($request);

        $old_variables = $classe->getVariables();
        if ($old_variables) {
            foreach($old_variables as $old_variable) {
                $old_variable->removeClasse($classe);
                $em->persist($old_variable);
            }
            $em->flush();
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $classe->setUpdateUser($this->getUser());
            try {
                foreach ($classe->getVariables() as $variable) {
                    $variable->addClasse($classe);
                    $em->persist($variable);
                }  
            
                $em->persist($classe);
                $em->flush();

                $this->addFlash('success', $translator->trans('classe.flash.updated'));

                return $this->redirectToRoute('admin_classe_show', [
                    'id'=>$classe->getId()
                ]);
            } catch (DBALException $exception) {
                $this->addFlash('error', $exception->getMessage());
            }
        }

        return $this->render('TemplateManagement/Classe/edit.html.twig', [
            'classe' => $classe,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Desactive une classe
     * @Route("/disable/{id}", name="admin_classe_disable", methods={"POST"})
     * @IsGranted("CLASSE_DISABLE", subject="classe", message="You cannot disable this classe.")
     */
    public function disable(Request $request, Classe $classe, TranslatorInterface $translator)
    {
        $form = $this->createForm(ClasseShowType::class, $classe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            try {
                if ($classe->getIsValid()) {         
                    $classe->setIsValid(false);
                    $this->addFlash('success', $translator->trans('classe.flash.disabled'));
                } else {
                    $classe->setIsValid(true);
                    $this->addFlash('success', $translator->trans('classe.flash.enabled'));
                }

                $classe->setUpdateUser($this->getUser());
                $classe->setUpdateDate(new \Datetime());               
                $em->persist($classe);
                $em->flush();
                return $this->redirectToRoute('admin_classe_index');
            } catch (DBALException $exception) {
                $this->addFlash('error', $exception->getMessage());
            }
        }

        if ($classe->getIsValid()) {
            return $this->redirectToRoute('admin_classe_show', ['id'=>$classe->getId(), 'action'=>'disable']);
        } else {
            return $this->redirectToRoute('admin_classe_show', ['id'=>$classe->getId(), 'action'=>'enable']);
        }  
    }

    /**
     * @Route("/delete/{id}", name="admin_classe_delete", methods={"POST"})
     * @IsGranted("CLASSE_DELETE", subject="classe", message="You cannot delete this classe.")
     */
    public function delete(Request $request, Classe $classe, TranslatorInterface $translator): Response
    {
        $form = $this->createForm(ClasseShowType::class, $classe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            try {
                $classe->setUpdateUser($this->getUser());     
                $classe->setIsValid(false);
                $classe->setIsDeleted(true);                           
                $em->persist($classe);
                $em->flush();

                $this->addFlash('success', $translator->trans('classe.flash.deleted'));
                return $this->redirectToRoute('admin_classe_index');

            } catch (DBALException $exception) {
                $this->addFlash('error', $exception->getMessage());
            }
        }

        return $this->redirectToRoute('admin_classe_show', ['id'=>$classe->getId(), 'action'=>'delete']);
    }

    /**
     * @Route("/download", name="admin_classe_download", methods={"GET"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function download(ClasseRepository $classeRepository, Environment $twig): Response
    {
        $content = $twig->render('TemplateManagement/Classe/download.html.twig', [
            'classes' => $classeRepository->findBy(['isDeleted'=>false, 'isValid'=>true], ['title'=>'DESC'])
        ]);

        return new Response($content, 200, [
            'Content-Type' => 'application/force-download;charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="classes_' . (new \Datetime())->format('d-M-Y H:i:s') . '.xls"'
        ]);        

    }
}

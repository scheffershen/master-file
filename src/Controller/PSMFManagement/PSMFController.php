<?php

namespace App\Controller\PSMFManagement;

use App\Entity\LovManagement\EntitType;
use App\Entity\PSMFManagement\PSMF;
use App\Form\PSMFManagement\PSMFType;
use App\Repository\PSMFManagement\PSMFRepository;
use App\Repository\TemplateManagement\VariableRepository;
use Doctrine\DBAL\Exception as DBALException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security as SymfonySecurity;

/**
 * @Route("/admin/psmf")
 */
class PSMFController extends AbstractController
{    
    /**
     * @Route("/index", name="admin_psmf_index", methods={"GET"})
     * @Security("is_granted('ROLE_USER')")
     */
    public function index(SymfonySecurity $security, PSMFRepository $pSMFRepository, VariableRepository $variableRepository): Response
    {
        if ($security->isGranted('ROLE_ADMIN')) {
            return $this->render('PSMFManagement/PSMF/index.html.twig', [
                'psmfs' => $pSMFRepository->findBy(['isDeleted'=>false], ['updateDate'=>'DESC']),
                'variables' => $variableRepository->findEquivalencesLocales(),
                'variablesGlobale' => $variableRepository->findEquivalencesGlobales(),
            ]);
        } else {
            return $this->render('PSMFManagement/PSMF/index.html.twig', [
                'psmfs' => $pSMFRepository->findPublishedDocumentByUser($this->getUser()),
                'variables' => $variableRepository->findEquivalencesLocales(),
                'variablesGlobale' => $variableRepository->findEquivalencesGlobales(),
            ]);
        }            
    }

    /**
     * @Route("/new", name="admin_psmf_new", methods={"GET","POST"})
     * @Security("is_granted('ROLE_UTILISATEUR')")
     */
    public function new(Request $request, TranslatorInterface $translator): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $um = $entityManager->getRepository("App\Entity\LovManagement\EntitType")->findOneBy(['code'=>EntitType::UM]);
        
        $pSMF = new PSMF();
        $pSMF->setEuqppvEntity($um);

        $form = $this->createForm(PSMFType::class, $pSMF);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $pSMF->setCreateUser($this->getUser());
                $pSMF->setUpdateUser($this->getUser());  
                             
                $entityManager->persist($pSMF);
                $entityManager->flush();

                $this->addFlash('success', $translator->trans('psmf.flash.created'));

                return $this->redirectToRoute('admin_psmf_correspondance_locale3_edit', ['psmf'=>$pSMF->getId()]);
            } catch (DBALException $exception) {
                $this->addFlash('error', $exception->getMessage());
            } catch (\Throwable $exception) {
                $this->addFlash('error', $exception->getMessage());
            }             
        }

        return $this->render('PSMFManagement/PSMF/new.html.twig', [
            'psmf' => $pSMF,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Desactive/Active un psmf
     * @Route("/disable/{id}", name="admin_psmf_disable", methods={"GET","POST"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function disable(PSMF $pSMF, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();

        if ($pSMF->getIsValid()) {
            $pSMF->setIsValid(false);
            $this->addFlash('success', $translator->trans('psmf.flash.disable'));
        } else {
            $pSMF->setIsValid(true);
            $this->addFlash('success', $translator->trans('psmf.flash.enable'));
        }

        try {
            $em->persist($pSMF);
            $em->flush();
        } catch (DBALException $exception) {
            $this->addFlash('error', $exception->getMessage());
        }

        return $this->redirectToRoute('admin_psmf_index');
    }
 
}

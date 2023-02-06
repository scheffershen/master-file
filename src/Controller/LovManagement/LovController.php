<?php

namespace App\Controller\LovManagement;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\DBAL\Exception as DBALException;

/**
 * LovController
 */
class LovController extends AbstractController
{
    /**
     * Affiche la liste dune LOV (List Of Values) du projet.
     * @Route("/admin/lov/list/{entity}", name="admin_lov_list", methods={"GET","HEAD"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function list($entity = null)
    {
        if ($entity == null) {
            $entity = 'Gender';
        }
        
        return $this->render('LovManagement/list.html.twig', [
                'lovs' => $this->getDoctrine()->getManager()->getRepository("App\Entity\LovManagement\\" . $entity)->findBy([], ['sort'=>'ASC']),
                'entity' => $entity,
        ]);
    }

    /**
     * Affiche le formulaire modification d'une LOV (List Of Values) du projet.
     * @Route("/admin/lov/edit/{entity}/{id}", name="admin_lov_edit", methods={"GET","POST"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function edit(Request $request, $entity, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $value = $em->getRepository("App\Entity\LovManagement\\" . $entity)->findOneBy(['id' => $id]);
        $form = $this->createForm("App\Form\LovManagement\LovType", $value);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $user = $this->getUser();
                $value->setUpdateUser($user);
                $value->setUpdateDate(new \Datetime());

                try {
                    $em->persist($value);
                    $em->flush();

                    $this->addFlash('success', 'lov.flash.updated');

                    return $this->redirect($this->generateUrl('admin_lov_list', [
                        'entity' => $entity,
                    ]));
                } catch (DBALException $exception) {
                    $this->addFlash('error', $exception->getMessage());
                }
            }
        }

        return $this->render('LovManagement/edit.html.twig', [
                'value' => $value,
                'form' => $form->createView(),
                'entity' => $entity,
                'lovs' =>  $em->getRepository("App\Entity\LovManagement\\" . $entity)->findBy([], ['sort'=>'ASC'])
        ]);
    }

    /**
     * Affiche le formulaire d'ajout d'une LOV (List Of Values) du projet.
     * @Route("/admin/lov/add/{entity}", name="admin_lov_add", methods={"GET","POST"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function add(Request $request, $entity)
    {
        $em = $this->getDoctrine()->getManager();
        $namespaceEntity = $em->getRepository("App\Entity\LovManagement\\" . $entity)->getClassName();
        $ordre = sizeof($em->getRepository("App\Entity\LovManagement\\" . $entity)->findBy([]));

        $value = new $namespaceEntity();
        $value->setSort($ordre);
        $form = $this->createForm("App\Form\LovManagement\LovType", $value);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $user = $this->getUser();
                $value->setCreateUser($user);
                $value->setUpdateUser($user);

                $value->setCreateDate(new \Datetime());
                $value->setUpdateDate(new \Datetime());
                try {
                    $em->persist($value);
                    $em->flush();

                    $this->addFlash('success', 'lov.flash.created');

                    return $this->redirect($this->generateUrl('admin_lov_list', [
                            'entity' => $entity,
                        ]));
                } catch (DBALException $exception) {
                    $this->addFlash('error', $exception->getMessage());
                }
            }
        }

        return $this->render('LovManagement/add.html.twig', [
                'value' => $value,
                'form' => $form->createView(),
                'entity' => $entity,
                'lovs' =>  $em->getRepository("App\Entity\LovManagement\\" . $entity)->findBy([], ['sort'=>'ASC'])
        ]);
    }

    /**
     * Desactive logiquement une valeur de LOV
     * @Route("/admin/lov/disable/{entity}/{id}", name="admin_lov_disable", methods={"GET","POST"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function disable($entity, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $valueOnList = $em->getRepository("App\Entity\LovManagement\\" . $entity)->findOneBy(['id' => $id]);

        $user = $this->getUser();
        $valueOnList->setUpdateUser($user);
        $valueOnList->setUpdateDate(new \Datetime());

        if ($valueOnList->getIsValid()) {
            $valueOnList->setIsValid(false);
            $this->addFlash('success', 'lov.flash.disable');
        } else {
            $valueOnList->setIsValid(true);
            $this->addFlash('success', 'lov.flash.enable');
        }

        try {
            $em->persist($valueOnList);
            $em->flush();
        } catch (DBALException $exception) {
            $this->addFlash('error', $exception->getMessage());
        }

        return $this->redirect($this->generateUrl('admin_lov_list', [
            'entity' => $entity,
        ]));
    }
}

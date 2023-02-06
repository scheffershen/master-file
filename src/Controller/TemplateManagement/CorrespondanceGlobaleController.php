<?php

namespace App\Controller\TemplateManagement;

use App\Entity\LovManagement\TypeVariable; 
use App\Entity\TemplateManagement\Correspondance;
use App\Entity\TemplateManagement\CorrespondanceGlobaleHistory;
use App\Form\TemplateManagement\CorrespondanceGlobaleHistoryType;
use App\Repository\TemplateManagement\VariableRepository;
use App\Repository\PSMFManagement\PSMFHistoryRepository;
use App\Message\TemplateManagement\CorrespondanceGlobaleHistoryUpdated;
use App\Serializer\TemplateManagement\CorrespondanceGlobaleHistorySerializer;
use Doctrine\DBAL\Exception as DBALException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * @Route("/admin/correspondance")
 */
class CorrespondanceGlobaleController extends AbstractController
{
    public const max_history_per_page = 50;

    private $slugger;
    private $kernel;
    private $messageBus;
    private $correspondanceGlobaleHistorySerializer;
    private $pSMFHistoryRepository;
    private $translator;

    public function __construct(SluggerInterface $slugger, KernelInterface $kernel, CorrespondanceGlobaleHistorySerializer $correspondanceGlobaleHistorySerializer, PSMFHistoryRepository $pSMFHistoryRepository, MessageBusInterface $messageBus, TranslatorInterface $translator)
    {
        $this->slugger = $slugger;
        $this->kernel = $kernel;
        $this->correspondanceGlobaleHistorySerializer = $correspondanceGlobaleHistorySerializer;
        $this->messageBus = $messageBus;
        $this->pSMFHistoryRepository = $pSMFHistoryRepository;
        $this->translator = $translator;        
    }

    /**
     * @Route("/globale3/new", name="admin_correspondance_globale3", methods={"GET","POST"})
     * @Security("is_granted('ROLE_ADMIN')")     
     */
    public function new(Request $request, VariableRepository $variableRepository): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $correspondanceGlobaleHistory = new CorrespondanceGlobaleHistory();
        $correspondanceGlobaleHistory->setIsMajeur(false);
        $variables = $variableRepository->findEquivalencesGlobales();
        foreach ($variables as $variable) {
            if ($variable->isValid()) {
                if ($variable->getCorrespondanceGlobale()) {
                    $correspondanceGlobaleHistory->addCorrespondance($variable->getCorrespondanceGlobale());
                } else {
                    $correspondance = new Correspondance();
                    $correspondance->setVariable($variable);
                    $correspondance->setCreateUser($this->getUser());
                    $correspondance->setUpdateUser($this->getUser());                 
                    $correspondanceGlobaleHistory->addCorrespondance($correspondance);
                    $entityManager->persist($correspondance);
                }
            }
        }
        $entityManager->flush();

        $old = $this->correspondanceGlobaleHistorySerializer->serialize($correspondanceGlobaleHistory);
        
        $form = $this->createForm(CorrespondanceGlobaleHistoryType::class, $correspondanceGlobaleHistory);
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
                try {
                    foreach ($correspondanceGlobaleHistory->getCorrespondances() as $key => $correspondance) {
                        if ($correspondance->getVariable()->isValid()) {
                            switch ($correspondance->getVariable()->getType()->getCode()) {
                                case TypeVariable::IMAGE:
                                    if ( $_FILES['correspondance_globale_history']['error']['correspondances'][$key]['upload'] == UPLOAD_ERR_OK ) {
                                          $pieces = explode(".", $_FILES['correspondance_globale_history']['name']['correspondances'][$key]['upload']);
                                          $safeFilename = $this->slugger->slug($pieces[0]).'.'.$pieces[1];
                                          move_uploaded_file($_FILES['correspondance_globale_history']['tmp_name']['correspondances'][$key]['upload'], $this->kernel->getProjectDir() .'/data/' . $safeFilename);
                                         $correspondance->setValueLocal($safeFilename);
                                         $entityManager->persist($correspondance); 
                                         $entityManager->flush();
                                    }
                                    break;
                            }
                        }     
                    }

                $correspondanceGlobaleHistory->setCreateUser($this->getUser());
                $correspondanceGlobaleHistory->setUpdateUser($this->getUser());   
                $entityManager->persist($correspondanceGlobaleHistory);                     
                $entityManager->flush();

                if ($correspondanceGlobaleHistory->getIsMajeur()) {
                    $this->messageBus->dispatch(new CorrespondanceGlobaleHistoryUpdated('edit', $old, $correspondanceGlobaleHistory));
                }
                
                $this->addFlash('success', $this->translator->trans('correspondance.flash.updated'));

                return $this->redirectToRoute('admin_correspondance_globale3_show');
            } catch (DBALException $exception) {
                $this->addFlash('error', $exception->getMessage());
            } catch (\Throwable $exception) {
                $this->addFlash('error', $exception->getMessage());
            }             
        }

        return $this->render('TemplateManagement/CorrespondanceGlobaleHistory/new.html.twig', [
            'form' => $form->createView(),
            'variables' => $variables,
        ]);
    }

    /**
     * @Route("/globale3", name="admin_correspondance_globale3_show", methods={"GET"})
     * @Security("is_granted('ROLE_ADMIN')")     
     */
    public function show(VariableRepository $variableRepository): Response
    {
        return $this->render('TemplateManagement/CorrespondanceGlobaleHistory/show.html.twig', [
            'variables' => $variableRepository->findEquivalencesGlobales(),
        ]);
    }

    /**
     * @Route("/globale3/history", name="admin_correspondance_globale3_history", defaults={"page": "1"}, methods={"GET"})
     * @Route("/globale3/history/{page}", requirements={"page"="\d+"}, name="admin_correspondance_globale3_history_paginated")     
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function history(int $page): Response
    {
        $total = $this->pSMFHistoryRepository->getTotalByVariableGlobaleModification();

        $pagination = [
            'page' => $page,
            'route' => 'admin_correspondance_globale3_history_paginated',
            'pages_count' => ceil($total / self::max_history_per_page),
            'route_params' => [],
        ];

        $pSMFVariableHistories = $this->pSMFHistoryRepository->findByVariableGlobaleModification($page, self::max_history_per_page);  

        return $this->render('TemplateManagement/CorrespondanceGlobaleHistory/history.html.twig', [
            'pSMFVariableHistories' => $pSMFVariableHistories,
            'pagination' => $pagination, 
        ]);
    }
}

<?php

namespace App\Controller\PSMFManagement;

use App\Entity\LovManagement\TypeVariable; 
use App\Entity\PSMFManagement\PSMF;
use App\Entity\TemplateManagement\Variable;
use App\Entity\TemplateManagement\Correspondance;
use App\Entity\TemplateManagement\CorrespondanceLocaleHistory;
use App\Entity\TemplateManagement\Template;
use App\Form\TemplateManagement\CorrespondanceLocaleHistoryType;
use App\Form\TemplateManagement\CorrespondanceType;
use App\Repository\TemplateManagement\CorrespondanceRepository;
use App\Repository\TemplateManagement\SectionRepository;
use App\Repository\TemplateManagement\VariableRepository;
use App\Message\TeamsMessage;
use App\Message\TemplateManagement\CorrespondanceLocaleHistoryUpdated;
use App\Serializer\TemplateManagement\CorrespondanceLocaleHistorySerializer;
use Doctrine\DBAL\Exception as DBALException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Twig\Environment;

/**
 * @Route("/admin/psmf")
 */
class PSMFCorrespondanceLocaleController extends AbstractController
{
    private $slugger;
    private $twig;
    private $kernel;
    private $variableRepository;
    private $messageBus;
    private $correspondanceRepository;
    private $sectionRepository;
    private $correspondanceLocaleHistorySerializer;
    private $translator;
    private $router;

    public function __construct(SluggerInterface $slugger, KernelInterface $kernel, CorrespondanceRepository $correspondanceRepository, VariableRepository $variableRepository, SectionRepository $sectionRepository, CorrespondanceLocaleHistorySerializer $correspondanceLocaleHistorySerializer, MessageBusInterface $messageBus, UrlGeneratorInterface $router, TranslatorInterface $translator, Environment $twig)
    {
        $this->slugger = $slugger;
        $this->twig = $twig;
        $this->kernel = $kernel;
        $this->variableRepository = $variableRepository;
        $this->correspondanceRepository = $correspondanceRepository;
        $this->sectionRepository = $sectionRepository;
        $this->correspondanceLocaleHistorySerializer = $correspondanceLocaleHistorySerializer;
        $this->messageBus = $messageBus;
        $this->router = $router;
        $this->translator = $translator;
    }

    /**
     * version 3 psmf_correspondance_locale3_edit
     * @Route("/locale3/{psmf}", name="admin_psmf_correspondance_locale3_show", methods={"GET"})
     * @Security("is_granted('ROLE_UTILISATEUR')")      
     */
    public function show(PSMF $psmf): Response
    {
        return $this->render('TemplateManagement/CorrespondanceLocaleHistory/show.html.twig', [
            'psmf' => $psmf,
            'variables' => $this->variableRepository->findEquivalencesLocales(),
            'variablesLocalesDisables' => $this->variableRepository->findEquivalencesLocales(false),
            'variablesGlobale' => $this->variableRepository->findEquivalencesGlobales(),
            'sections' => $this->sectionRepository->findBy([
                    'template' => Template::TEMPLATE_ID,
                    'parent' => NULL,
                    'isDeleted' => false,
                    'isValid' => true
                ],[
                    'position' => 'ASC',
                ])            
        ]);
    }

    /**
     * version 3 psmf_correspondance_locale3_edit
     * @Route("/locale3/{psmf}/edit", name="admin_psmf_correspondance_locale3_edit", methods={"GET","POST"})
     * @Security("is_granted('ROLE_UTILISATEUR')") 
     */
    public function edit(Request $request, PSMF $psmf): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $correspondanceLocaleHistory = new CorrespondanceLocaleHistory();
        $correspondanceLocaleHistory->setPsmf($psmf); 
        $correspondanceLocaleHistory->setIsMajeur(false);
        $variables = $this->variableRepository->findEquivalencesLocales();
        foreach ($variables as $variable) {
            if ($variable->isValid()) {
                $correspondance = $this->correspondanceRepository->findOneBy(['psmf' => $psmf, 'variable' => $variable]);
                if ($correspondance) {
                    $correspondanceLocaleHistory->addCorrespondance($correspondance);
                } else {
                    $correspondance = new Correspondance();
                    $correspondance->setVariable($variable);
                    $correspondance->setPsmf($psmf);
                    $correspondance->setCreateUser($this->getUser());
                    $correspondance->setUpdateUser($this->getUser());                 
                    $correspondanceLocaleHistory->addCorrespondance($correspondance);
                    $entityManager->persist($correspondance);
                }
            }
        }
        $entityManager->flush();

        $old = $this->correspondanceLocaleHistorySerializer->serialize($correspondanceLocaleHistory);

        $form = $this->createForm(CorrespondanceLocaleHistoryType::class, $correspondanceLocaleHistory);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                foreach ($correspondanceLocaleHistory->getCorrespondances() as $key => $correspondance) {
                    if ($correspondance->getVariable()->isValid()) {
                        switch ($correspondance->getVariable()->getType()->getCode()) {
                            case TypeVariable::IMAGE:
                                if ( $_FILES['correspondance_locale_history']['error']['correspondances'][$key]['upload'] == UPLOAD_ERR_OK ) {
                                    $pieces = explode(".", $_FILES['correspondance_locale_history']['name']['correspondances'][$key]['upload']);
                                    $safeFilename = $this->slugger->slug($pieces[0]).'.'.$pieces[1];
                                      move_uploaded_file($_FILES['correspondance_locale_history']['tmp_name']['correspondances'][$key]['upload'], $this->kernel->getProjectDir() .'/data/' . $safeFilename);
                                    $correspondance->setValueLocal($safeFilename);
                                    $entityManager->persist($correspondance); 
                                    $entityManager->flush();
                                }
                                break;
                        }   
                    }     
                }

                $correspondanceLocaleHistory->setCreateUser($this->getUser());
                $correspondanceLocaleHistory->setUpdateUser($this->getUser());   
                $entityManager->persist($correspondanceLocaleHistory); 
  
                //SYS_PSMF_LAST_MAJ
                $psmf->setUpdateDate(); 
                $entityManager->persist($psmf);                    
                $entityManager->flush();

                if ($correspondanceLocaleHistory->getIsMajeur()) {
                    $this->messageBus->dispatch(new CorrespondanceLocaleHistoryUpdated('edit', $old, $correspondanceLocaleHistory));

                }
                
                //$this->messageBus->dispatch(new ChatMessage($this->router->generate('admin_psmf_correspondance_locale3_show',['psmf' => $psmf->getId()], 0), $this->translator->trans('correspondance.flash.updated'), $psmf->getTitle()));

                $this->addFlash('success', $this->translator->trans('correspondance.flash.updated'));

                return $this->redirectToRoute('admin_psmf_correspondance_locale3_show', ['psmf'=>$psmf->getId()]);
            } catch (DBALException $exception) {
                $this->addFlash('error', $exception->getMessage());
            } catch (\Throwable $exception) {
                $this->addFlash('error', $exception->getMessage());
            } 
        }

        return $this->render('TemplateManagement/CorrespondanceLocaleHistory/new.html.twig', [
            'psmf' => $psmf,
            'form' => $form->createView(),
            'variables' => $this->variableRepository->findEquivalencesLocales(),
            'variablesLocalesDisables' => $this->variableRepository->findEquivalencesLocales(false),
            'variablesGlobale' => $this->variableRepository->findEquivalencesGlobales(),
            'sections' => $this->sectionRepository->findBy([
                    'template' => Template::TEMPLATE_ID,
                    'parent' => NULL,
                    'isDeleted' => false,
                    'isValid' => true
                ],[
                    'position' => 'ASC',
                ])
        ]);
    }

    /**
     * version 3 psmf_correspondance_locale3_image_delete
     * @Route("/locale3Image/{psmf}/{variable}/{correspondance}/delete", name="admin_psmf_correspondance_locale3_image_delete", methods={"GET","POST"})
     * @Security("is_granted('ROLE_UTILISATEUR')") 
     */
    public function correspondanceLocaleImageDelete(Request $request, PSMF $psmf, Variable $variable, Correspondance $correspondance): Response
    {
        $correspondanceLocaleHistory = new CorrespondanceLocaleHistory();
        $correspondanceLocaleHistory->setPsmf($psmf); 
        $correspondanceLocaleHistory->addCorrespondance($correspondance);
        $old = $this->correspondanceLocaleHistorySerializer->serialize($correspondanceLocaleHistory);

        $form = $this->createForm(CorrespondanceType::class, $correspondance, ['variable' => $variable]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();

            try {
                // set value to null
                $correspondance->setValueLocal(null);                
                $correspondance->setCreateUser($this->getUser());
                $correspondance->setUpdateUser($this->getUser()); 
                $entityManager->persist($correspondance);  
                
                // update correspondance locale history
                $correspondanceLocaleHistory->setReason($correspondance->getReason());
                $correspondanceLocaleHistory->setCreateUser($this->getUser());
                $correspondanceLocaleHistory->setUpdateUser($this->getUser());   
                $entityManager->persist($correspondanceLocaleHistory);
                
                // update psmf
                $psmf->setUpdateDate(); 
                $entityManager->persist($psmf);  

                $entityManager->flush();

                $this->addFlash('success', $this->translator->trans('correspondance.flash.updated'));

                $this->messageBus->dispatch(new CorrespondanceLocaleHistoryUpdated('delete', $old, $correspondanceLocaleHistory));

                return $this->redirectToRoute('admin_psmf_correspondance_locale3_show', ['psmf'=>$psmf->getId()]);
            } catch (DBALException $exception) {
                $this->addFlash('error', $exception->getMessage());
            } catch (\Throwable $exception) {
                $this->addFlash('error', $exception->getMessage());
            } 
        }

        return $this->render('TemplateManagement/CorrespondanceLocaleHistory/correspondance.html.twig', [
            'psmf' => $psmf,
            'variable' => $variable,
            'correspondance' => $correspondance,
            'form' => $form->createView(),
        ]);
    }

    /**
     * version 3 psmf_correspondance_locale3_download
     * @Route("/locale3Download/{psmf}/{format}", name="admin_psmf_correspondance_locale3_download", methods={"GET"})
     * @Security("is_granted('ROLE_UTILISATEUR')")     
     */
    public function download(PSMF $psmf, string $format): Response
    {
        ini_set('memory_limit', -1);
        $content = $this->twig->render('TemplateManagement/CorrespondanceLocaleHistory/download.html.twig', [
            'psmf' => $psmf,
            'variables' => $this->variableRepository->findEquivalencesLocales(),
            'variablesGlobale' => $this->variableRepository->findEquivalencesGlobales(),
            'sections' => $this->sectionRepository->findBy([
                    'template' => Template::TEMPLATE_ID,
                    'parent' => NULL,
                    'isDeleted' => false,
                    'isValid' => true
                ],[
                    'position' => 'ASC',
                ])            
        ]);

        return new Response($content, 200, [
            'Content-Type' => 'application/force-download;charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'. $this->slugger->slug($psmf->getClient()) . '_' . $this->slugger->slug($psmf->getTitle()) . '_' . (new \Datetime())->format('d-M-Y H:i:s') . '.xls"'
        ]);          
    }    
}

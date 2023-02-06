<?php

namespace App\Controller\UserManagement;

use App\Entity\Plateforme;
use App\Form\PlateformeType;
use App\Repository\PlateformeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * @Route("/admin/plateforme")
 */
class PlateformeController extends AbstractController
{
    private $kernel;
    private $messageBus;
    private $slugger;
    private $translator;
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager, MessageBusInterface $messageBus, TranslatorInterface $translator, KernelInterface $kernel, SluggerInterface $slugger)
    {
        $this->kernel = $kernel;
        $this->messageBus = $messageBus;  
        $this->slugger = $slugger;  
        $this->translator = $translator;
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/edit", name="admin_plateforme_edit", methods={"GET", "POST"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function edit(Request $request): Response
    {
        $plateforme = $this->entityManager->getRepository('App\Entity\Plateforme')->findOneBy(['code' => Plateforme::PSMF]);

        $form = $this->createForm(PlateformeType::class, $plateforme);
     
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plateforme->setUpdateUser($this->getUser());

            $uploadFile = $form->get('logo')->getData();
            
            if ($uploadFile) {
                $originalFilename = pathinfo($uploadFile->getClientOriginalName(), PATHINFO_FILENAME);

                $safeFilename = $this->slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$uploadFile->guessExtension();

                try {
                    $ds = DIRECTORY_SEPARATOR;
                    $uploadFile->move($this->kernel->getProjectDir() . $ds .'data'. $ds, $newFilename);
                    $plateforme->setLogoUri($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', $e->getMessage());
                }                
            }

            try {
                $this->entityManager->persist($plateforme);
                $this->entityManager->flush();

                $this->addFlash('success', $this->translator->trans('plateforme.flash.updated'));

                return $this->redirectToRoute('admin_plateforme_show');
            } catch (DBALException $exception) {
                $this->addFlash('error', $exception->getMessage());
            }
        }

        return $this->renderForm('Plateforme/edit.html.twig', [
            'plateforme' => $plateforme,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/show", name="admin_plateforme_show", methods={"GET"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function show(): Response
    {
        $plateforme = $this->entityManager->getRepository('App\Entity\Plateforme')->findOneBy(['code' => Plateforme::PSMF]);
        
        return $this->render('Plateforme/show.html.twig', [
            'plateforme' => $plateforme,
        ]);
    }
}

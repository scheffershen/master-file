<?php

namespace App\Controller;

use App\Entity\Plateforme;
use App\Repository\PlateformeRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/plateforme")
 */
class PlateformeController extends AbstractController
{
    private $plateformeRepository;

    public function __construct(PlateformeRepository $plateformeRepository)
    {
        $this->plateformeRepository = $plateformeRepository;
    }

    /**
     * @Route("/{id}", name="plateforme_apropos", defaults={"id": 1}, methods={"GET"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function apropos(Request $request, Plateforme $plateforme): Response
    {
        return $this->renderForm('Plateforme/apropos.html.twig', [
            'plateforme' => $plateforme,
        ]);
    }

    /**
     * Subtemplate embed controller
     */     
    public function logo(): Response
    {
        return $this->render('partials/_logo.html.twig', [
            'plateforme' => $this->plateformeRepository->findOneBy(['id' => 1])
        ]);        
    }

    /**
     * Subtemplate embed controller
     */     
    public function footer(): Response
    {
        return $this->render('partials/_apropos.html.twig', [
            'plateforme' => $this->plateformeRepository->findOneBy(['id' => 1])
        ]);        
    }
}

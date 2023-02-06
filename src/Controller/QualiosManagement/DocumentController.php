<?php

namespace App\Controller\QualiosManagement;

use App\Entity\Plateforme;
use App\Repository\QualiosManagement\DocumentRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

/**
 * @Route("/admin/qualios")
 */
class DocumentController extends AbstractController
{
    /**
     * @Route("/", name="admin_qualios_index", methods={"GET"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function index(DocumentRepository $documentRepository): Response
    {
        $em = $this->getDoctrine()->getManager();
        $plateforme = $em->getRepository('App\Entity\Plateforme')->findOneBy(['code' => Plateforme::PSMF]);

        // somehow create a Response object, like by rendering a template
        $response = $this->render('QualiosManagement/Document/index.html.twig', [
            'documents' => $documentRepository->findAll(),
            'plateforme' => $plateforme
        ]);

        // cache publicly for 3600 seconds
        $response->setPublic();
        $response->setMaxAge(3600);

        // (optional) set a custom Cache-Control directive
        $response->headers->addCacheControlDirective('must-revalidate', true);

        return $response;        
    }

    /**
     * @Route("/download", name="admin_qualios_download", methods={"GET"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function download(DocumentRepository $documentRepository, Environment $twig): Response
    {
        $content = $twig->render('QualiosManagement/Document/download.html.twig', [
            'documents' => $documentRepository->findBy([], ['reference'=>'ASC'])
        ]);

        return new Response($content, 200, [
            'Content-Type' => 'application/force-download;charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="qualios_' . (new \Datetime())->format('d-M-Y H:i:s') . '.xls"'
        ]);        

    }
}

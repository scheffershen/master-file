<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\KernelInterface;

class PrivateController extends AbstractController
{
    /**
     * return a security private upload.
     *
     * @Route("/upload/{format}/{upload}", defaults={"format": "origin"}, name="admin_private_upload", methods="GET")
     * Security("is_granted('ROLE_USER')")
     */
    public function upload(Request $request, string $upload, string $format="origin", KernelInterface $kernel): Response 
    {
        $fs = new Filesystem();

        if ($format == 'psmf') {            
            $filePath = $kernel->getProjectDir() . '/PSMD/' . $upload;            
        } elseif ($format == 'pdfSigne') { 
            $filePath = $kernel->getProjectDir() . '/PSMD_SIGNE/' . $upload;  
        } elseif ($format == 'origin') {
            $filePath = $kernel->getProjectDir() . '/data/' . $upload;            
        } else {
            $filePath = $kernel->getProjectDir() . '/data/'. $format .'/' . $upload;
        }
        
        if ($fs->exists($filePath)) {       
            $response = new BinaryFileResponse($filePath);
                $response->headers->set('Content-Type', mime_content_type($filePath));
                $response->setContentDisposition(
                    ResponseHeaderBag::DISPOSITION_INLINE,
                    basename($filePath)
                );
                return $response;            
        } 

        $filePath = $kernel->getProjectDir() . '/data/not-found.png';
        if ($fs->exists($filePath)) {       
            $response = new BinaryFileResponse($filePath);
                $response->headers->set('Content-Type', mime_content_type($filePath));
                $response->setContentDisposition(
                    ResponseHeaderBag::DISPOSITION_INLINE,
                    basename($filePath)
                );
                return $response;            
        } 
        throw new FileNotFoundException($filePath);
    }

    /**
     * return a image in word
     *
     * @Route("/upload4word/{format}/{upload}", defaults={"format": "origin"}, name="admin_public_upload", methods="GET")
     */
    public function word(Request $request, string $upload, string $format="origin", KernelInterface $kernel): Response 
    {
        $fs = new Filesystem();

        if ($format == 'psmf') {
            $filePath = $kernel->getProjectDir() . '/PSMD/' . $upload;            
        } elseif ($format == 'origin') {
            $filePath = $kernel->getProjectDir() . '/data/' . $upload;            
        } else {
            $filePath = $kernel->getProjectDir() . '/data/'. $format .'/' . $upload;
        }
        
        if ($fs->exists($filePath)) {       
            $response = new BinaryFileResponse($filePath);
                $response->headers->set('Content-Type', mime_content_type($filePath));
                $response->setContentDisposition(
                    ResponseHeaderBag::DISPOSITION_INLINE,
                    basename($filePath)
                );
                return $response;            
        } 

        $filePath = $kernel->getProjectDir() . '/data/not-found.png';
        if ($fs->exists($filePath)) {       
            $response = new BinaryFileResponse($filePath);
                $response->headers->set('Content-Type', mime_content_type($filePath));
                $response->setContentDisposition(
                    ResponseHeaderBag::DISPOSITION_INLINE,
                    basename($filePath)
                );
                return $response;            
        } 
        throw new FileNotFoundException($filePath);
    }
}

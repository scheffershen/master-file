<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class UtilsController extends AbstractController
{
    /**
     * return a user.
     *
     * @Route("/is_login", name="is_login", methods="POST")
     */
    public function isLogin(): JsonResponse 
    {
        return new JsonResponse([
            'authenticated' => $this->getUser() !== null,
        ]);
    }
      
}

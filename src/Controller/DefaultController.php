<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @codeCoverageIgnore
 */
class DefaultController extends AbstractController
{
    /**
     * home page.
     * @Route("/", name="home", methods="GET")
     */
    public function index(Request $request, Security $security): Response
    {
        if ($security->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('admin_published_document_actif');
        } 

        return $this->redirectToRoute('app_login');
    }

    /**
     * changeLocale.
     * @Route("/changelocal", name="change_locale", methods="GET")
     */
    public function changeLocale(Request $request, $_locale): Response
    {
        //$request = $request();
        $url = $request->query->get('_route') ?: 'home';

        $param = $request->query->get('_route_params');
        $param['_locale'] = $_locale;

        if ($url === 'app_login') {
            $param['change_locale'] = 1;
        }

        switch ($_locale) {
            case 'fr':
                $this->get('session')->set('_locale', 'en');
                $request->setLocale('en');
                break;
            case 'en':
                $this->get('session')->set('_locale', 'fr');
                $request->setLocale('fr');
                break;
            default:
                $this->get('session')->set('_locale', 'fr');
                $request->setLocale('fr');
                break;
        }

        return $this->redirectToRoute($url, $param);
    }
}

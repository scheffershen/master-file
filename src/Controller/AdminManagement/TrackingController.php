<?php

namespace App\Controller\AdminManagement;

use App\Entity\UserManagement\LoggedMessage;
use App\Entity\UserManagement\Tracking;
use App\Repository\UserManagement\TrackingRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

/**
 * @Route("/admin/tracking")
 */
class TrackingController extends AbstractController
{
    public const max_tracking_per_page = 15;
    private $trackingRepository;
    private $twig;

    public function __construct(TrackingRepository $trackingRepository, Environment $twig)
    {      
        $this->twig = $twig;
        $this->trackingRepository = $trackingRepository;
    }

    /**
     * @Route("/connexion", name="admin_tracking_connexion", defaults={"page": "1"},  methods={"GET"})
     * @Route("/connexion/{page}", requirements={"page"="\d+"}, name="admin_tracking_connexion_paginated")
     *
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function connexion(int $page): Response
    {
        $total = $this->trackingRepository->getTotalByLogin();

        $pagination = [
            'page' => $page,
            'route' => 'admin_tracking_connexion_paginated',
            'pages_count' => ceil($total / self::max_tracking_per_page),
            'route_params' => [],
        ];

        $trackings = $this->trackingRepository->findByLogin($page, self::max_tracking_per_page);

        return $this->render('AdminManagement/Tracking/connexion.html.twig', [
                'trackings' => $trackings,
                'pagination' => $pagination,
        ]);
    }

    /**
     * @Route("/connexionDownload", name="admin_tracking_connexion_download", methods={"GET"})
     *
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function connexionDownload(): Response
    {
        $content = $this->twig->render('AdminManagement/Tracking/connexion_excel.html.twig', [
            'trackings' => $this->trackingRepository->findAllByLogin()]);

        return new Response($content, 200, [
            'Content-Type' => 'application/force-download;charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="export_connexion_' . (new \Datetime())->format('d-M-Y H:i:s') . '.xls"'
        ]);
    }

    /**
     * @Route("/mailLog", name="admin_mail_log", defaults={"page": "1"},  methods={"GET"})
     * @Route("/mailLog/{page}", requirements={"page"="\d+"}, name="admin_mail_log_paginated")
     *
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function mailLog(int $page): Response
    {
        $manager = $this->getDoctrine()->getManager();
        $total = $manager->getRepository('App\Entity\UserManagement\LoggedMessage')->getTotal();

        $pagination = [
            'page' => $page,
            'route' => 'admin_mail_log_paginated',
            'pages_count' => ceil($total / self::max_tracking_per_page),
            'route_params' => [],
        ];

        $loggedMessages = $manager->getRepository('App\Entity\UserManagement\LoggedMessage')->findByPage($page, self::max_tracking_per_page);

        return $this->render('AdminManagement/Tracking/mailLog.html.twig', [
                'loggedMessages' => $loggedMessages,
                'pagination' => $pagination,
        ]);
    }

    /**
     * @Route("/mailLogDownload", name="admin_mail_log_download", methods={"GET"})
     *
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function mailLogDownload(): Response
    {
        $manager = $this->getDoctrine()->getManager();

        $content = $this->twig->render('AdminManagement/Tracking/mailLog_excel.html.twig', [
            'loggedMessages' => $manager->getRepository('App\Entity\UserManagement\LoggedMessage')->findAll()]);

        return new Response($content, 200, [
            'Content-Type' => 'application/force-download;charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="export_mail_log_' . (new \Datetime())->format('d-M-Y H:i:s') . '.xls"'
        ]);
    }

    /**
     * @Route("/mailLog/message/{id}", name="admin_mail_log_message",   methods={"GET"})
     *
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function mailLogMessage(LoggedMessage $loggedMessage): Response
    {
        return $this->render('AdminManagement/Tracking/mailLogMessage.html.twig', [
                'loggedMessage' => $loggedMessage
        ]);
    }    
}

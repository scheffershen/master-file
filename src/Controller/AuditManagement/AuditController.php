<?php

namespace App\Controller\AuditManagement;

use DH\DoctrineAuditBundle\Exception\AccessDeniedException;
use DH\DoctrineAuditBundle\Exception\InvalidArgumentException;
use DH\DoctrineAuditBundle\Helper\AuditHelper;
use DH\DoctrineAuditBundle\Reader\AuditReader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

class AuditController extends AbstractController
{
    /**
     * @Route("/admin/audits", name="admin_audit_list", methods={"GET"})
     */
    public function list(AuditReader $reader): Response
    {
        return $this->render('AuditManagement/audits.html.twig', [
            'audited' => $reader->getEntities(),
            'reader' => $reader,
        ]);
    }

    /**
     * @Route("/admin/audit/transaction/{hash}", name="admin_audit_show_transaction", methods={"GET"})
     *
     * @param string $hash
     *
     * @throws InvalidArgumentException
     * @throws \Doctrine\ORM\ORMException
     *
     * @return Response
     */
    public function showTransactionAction(string $hash, AuditReader $reader): Response
    {
        $audits = $reader->getAuditsByTransactionHash($hash);

        return $this->render('AuditManagement/transaction.html.twig', [
            'hash' => $hash,
            'audits' => $audits,
        ]);
    }

    /**
     * @Route("/admin/audit/{entity}/{id}", name="admin_audit_show_entity_history", methods={"GET"})
     *
     * @param Request $request
     * @param string $entity
     * @param int|string $id
     *
     * @throws InvalidArgumentException
     *
     * @return Response
     */
    public function showEntityHistoryAction(Request $request, string $entity, $id = null, AuditReader $reader): Response
    {
        $page = (int) $request->query->get('page', 1);
        $entity = AuditHelper::paramToNamespace($entity);

        if (!$reader->getConfiguration()->isAuditable($entity)) {
            throw $this->createNotFoundException();
        }

        try {
            $entries = $reader->getAuditsPager($entity, $id, $page, AuditReader::PAGE_SIZE);
        } catch (AccessDeniedException $e) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('AuditManagement/entity_history.html.twig', [
            'id' => $id,
            'entity' => $entity,
            'entity_name' => explode('\\', $entity),
            'paginator' => $entries,
        ]);
    }

    /**
     * export les liste d'audit.

     * @Route("/admin/audit_export/{entity}/{id}", name="admin_audit_export", methods={"GET","HEAD"})
     */
    public function export(Request $request, string $entity = 'App-Entity-UserManagement-User', $id = null, AuditReader $reader, Environment $twig)
    {
        $page = (int) $request->query->get('page', 1);
        $entity = AuditHelper::paramToNamespace($entity);

        if (!$reader->getConfiguration()->isAuditable($entity)) {
            throw $this->createNotFoundException();
        }

        try {
            $entries = $reader->getAuditsPager($entity, $id, $page, AuditReader::PAGE_SIZE);
        } catch (AccessDeniedException $e) {
            throw $this->createAccessDeniedException();
        }

        $content = [];

        $content = $twig->render('AuditManagement/excel.html.twig', [
            'id' => $id,
            'entity' => $entity,
            'entity_name' => explode('\\', $entity),
            'audits' => $entries]);

        return new Response($content, 200, [
            'Content-Type' => 'application/force-download;charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="export_audit_' . $entity . '_' . (new \Datetime())->format('d-M-Y H:i:s') . '.xls"'
        ]);
    }
}

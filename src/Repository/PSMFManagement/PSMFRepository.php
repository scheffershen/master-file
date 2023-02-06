<?php

namespace App\Repository\PSMFManagement;

use App\Entity\LovManagement\Status;
use App\Entity\UserManagement\User;
use App\Entity\PSMFManagement\PSMF;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PSMF|null find($id, $lockMode = null, $lockVersion = null)
 * @method PSMF|null findOneBy(array $criteria, array $orderBy = null)
 * @method PSMF[]    findAll()
 * @method PSMF[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PSMFRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PSMF::class);
    }

    /**
     * @return PSMF[] Returns an array of PSMF objects
     */
    public function findByUser(User $user)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.euqppvEntity = :user OR p.euQPPV = :user OR p.deputyEUQPPV = :user OR p.contactPvClient = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return PSMF[] Returns an array of PSMF objects
     */
    public function findPublishedDocumentByUser(User $user)
    {
        return $this->createQueryBuilder('p')
            ->join('p.client', 'c')
            ->where('p.isDeleted = false')
            ->andWhere('p.client IN (:client)')
            ->setParameter('client', $user->getClients())
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return PSMF[] Returns an array of PSMF objects
     */
    public function findPublishedDocumentByStatus(?User $user=null, Status $status)
    {
        if ($user) {
            return $this->createQueryBuilder('p')
            ->join('p.client', 'c')
            ->join('p.publishedDocuments', 'd')
            ->where('p.isDeleted = false')
            ->andWhere('p.client IN (:client)')
            ->andWhere('d.status = :status')
            ->setParameters(['client' => $user->getClients(), 'status' => $status])
            ->orderBy('d.version', 'DESC')
            ->getQuery()
            ->getResult()
            ;
        } else {
            return $this->createQueryBuilder('p')
            ->join('p.publishedDocuments', 'd')
            ->where('p.isDeleted = false')
            ->andWhere('d.status = :status')
            ->setParameters(['status' => $status])
            ->orderBy('d.version', 'DESC')
            ->getQuery()
            ->getResult()
            ;
        }
    }
   
    /**
     * @return PSMF[] Returns an array of PSMF objects
     */
    public function findPublishedDocumentActif(?User $user=null, $actifs) 
    {
        if ($user) {
            return $this->createQueryBuilder('p')
            ->join('p.client', 'c')
            ->join('p.publishedDocuments', 'd')
            ->where('p.isDeleted = false')
            ->andWhere('p.client IN (:client)')
            ->andWhere('d.status IN (:status)')
            ->setParameters(['client' => $user->getClients(), 'status' => $actifs])
            ->orderBy('d.version', 'DESC')
            ->getQuery()
            ->getResult()
            ;
        } else {
            return $this->createQueryBuilder('p')
            ->join('p.client', 'c')
            ->join('p.publishedDocuments', 'd')
            ->where('p.isDeleted = false')
            ->andWhere('d.status IN (:status)')
            ->setParameters(['status' => $actifs])
            ->orderBy('d.version', 'DESC')
            ->getQuery()
            ->getResult()
            ;
        }
    }
    /*
    public function findOneBySomeField($value): ?PSMF
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

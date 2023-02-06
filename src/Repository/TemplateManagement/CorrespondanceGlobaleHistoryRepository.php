<?php

namespace App\Repository\TemplateManagement;

use App\Entity\TemplateManagement\CorrespondanceGlobaleHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CorrespondanceGlobaleHistory|null find($id, $lockMode = null, $lockVersion = null)
 * @method CorrespondanceGlobaleHistory|null findOneBy(array $criteria, array $orderBy = null)
 * @method CorrespondanceGlobaleHistory[]    findAll()
 * @method CorrespondanceGlobaleHistory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CorrespondanceGlobaleHistoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CorrespondanceGlobaleHistory::class);
    }

    // /**
    //  * @return CorrespondanceGlobaleHistory[] Returns an array of CorrespondanceGlobaleHistory objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?CorrespondanceGlobaleHistory
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

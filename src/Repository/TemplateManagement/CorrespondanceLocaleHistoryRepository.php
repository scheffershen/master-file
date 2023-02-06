<?php

namespace App\Repository\TemplateManagement;

use App\Entity\TemplateManagement\CorrespondanceLocaleHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CorrespondanceLocaleHistory|null find($id, $lockMode = null, $lockVersion = null)
 * @method CorrespondanceLocaleHistory|null findOneBy(array $criteria, array $orderBy = null)
 * @method CorrespondanceLocaleHistory[]    findAll()
 * @method CorrespondanceLocaleHistory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CorrespondanceLocaleHistoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CorrespondanceLocaleHistory::class);
    }

    // /**
    //  * @return CorrespondanceLocaleHistory[] Returns an array of CorrespondanceLocaleHistory objects
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
    public function findOneBySomeField($value): ?CorrespondanceLocaleHistory
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

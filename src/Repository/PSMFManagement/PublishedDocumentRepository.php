<?php

namespace App\Repository\PSMFManagement;

use App\Entity\PSMFManagement\PublishedDocument;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PublishedDocument|null find($id, $lockMode = null, $lockVersion = null)
 * @method PublishedDocument|null findOneBy(array $criteria, array $orderBy = null)
 * @method PublishedDocument[]    findAll()
 * @method PublishedDocument[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PublishedDocumentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PublishedDocument::class);
    }

    // /**
    //  * @return PublishedDocument[] Returns an array of PublishedDocument objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?PublishedDocument
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

<?php

namespace App\Repository;

use App\Entity\Plateforme;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Plateforme|null find($id, $lockMode = null, $lockVersion = null)
 * @method Plateforme|null findOneBy(array $criteria, array $orderBy = null)
 * @method Plateforme[]    findAll()
 * @method Plateforme[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PlateformeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Plateforme::class);
    }

    // /**
    //  * @return Plateforme[] Returns an array of Plateforme objects
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
    public function findOneBySomeField($value): ?Plateforme
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

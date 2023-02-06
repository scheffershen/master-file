<?php

namespace App\Repository\LovManagement;

use App\Entity\LovManagement\Status;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class StatusRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Status::class);
    }

    /**
     * @return Status[] Returns an array of Status objects
     */
    public function findByActifs()
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.code IN (:codes)')
            ->setParameter('codes', [Status::PUBLISHED, Status::DOWNLOADED, Status::APPLICABLE])
            ->getQuery()
            ->getResult()
        ;
    }
}

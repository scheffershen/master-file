<?php

namespace App\Repository\LovManagement;

use App\Entity\LovManagement\LocalQPPV;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class LocalQPPVRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LocalQPPV::class);
    }

}

<?php

namespace App\Repository\LovManagement;

use App\Entity\LovManagement\QualiosStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class QualiosStatusRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, QualiosStatus::class);
    }

}

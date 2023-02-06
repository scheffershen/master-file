<?php

namespace App\Repository\UserManagement;

use App\Entity\UserManagement\LoggedMessage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class LoggedMessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LoggedMessage::class);
    }

    public function getTotal()
    {
        $aResultTotal = $this->getEntityManager()
                ->createQuery('SELECT COUNT(L) FROM App\Entity\UserManagement\LoggedMessage L')
                ->setMaxResults(1)
                ->getSingleScalarResult();

        return $aResultTotal;
    }

    public function findByPage(int $page = 1, int $maxperpage = 10)
    {
        $query = $this->getEntityManager()
            ->createQuery(
                'SELECT L FROM App\Entity\UserManagement\LoggedMessage L ORDER BY L.date DESC'
            )
            ->setFirstResult(($page - 1) * $maxperpage)
            ->setMaxResults($maxperpage);

        try {
            $entities = $query->getResult();
        } catch (\Doctrine\ORM\NoResultException $e) {
            $entities = null;
        }

        return $entities;
    }

    public function findAll()
    {
        $queryBuilder = $this->createQueryBuilder('L')
            ->select('L')
            ->orderBy('L.date', 'DESC');

        $limit = 1000;
        $offset = 0;

        while (true) {
            $queryBuilder->setFirstResult($offset);
            $queryBuilder->setMaxResults($limit);

            $interactions = $queryBuilder->getQuery()->getResult();

            if (count($interactions) === 0) {
                break;
            }

            foreach ($interactions as $interaction) {
                yield $interaction;
                $this->_em->detach($interaction);
            }

            $offset += $limit;
        }
    }    
}

<?php

namespace App\Repository\UserManagement;

use App\Entity\UserManagement\User;
use App\Entity\UserManagement\Tracking;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TrackingRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tracking::class);
    }

    public function getTotalByLogin()
    {
        $aResultTotal = $this->getEntityManager()
            ->createQuery(
                'SELECT COUNT(T) FROM App\Entity\UserManagement\Tracking T WHERE T.action = :login OR T.action = :loginFailure ORDER BY T.created DESC')
            ->setParameters(['login' => 'loginAction', 'loginFailure' => 'loginFailureAction'])            
            ->setMaxResults(1)
            ->getSingleScalarResult();

        return $aResultTotal;
    }

    public function findByLogin(int $page = 1, int $maxperpage = 8)
    {
        $query = $this->getEntityManager()
            ->createQuery(
                'SELECT T FROM App\Entity\UserManagement\Tracking T WHERE T.action = :login OR T.action = :loginFailure ORDER BY T.created DESC'
            )
            ->setParameters(['login' => 'loginAction', 'loginFailure' => 'loginFailureAction'])
            ->setFirstResult(($page - 1) * $maxperpage)
            ->setMaxResults($maxperpage);

        try {
            $entities = $query->getResult();
        } catch (\Doctrine\ORM\NoResultException $e) {
            $entities = null;
        }

        return $entities;
    }
    
    public function findAllByLogin()
    {
        $queryBuilder = $this->createQueryBuilder('T')
            ->select('T')
            ->where('T.action = :login OR T.action = :loginFailure')
            ->setParameters(['login' => 'loginAction', 'loginFailure' => 'loginFailureAction'])
            ->orderBy('T.created', 'DESC');

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

    public function getLastAccess(int $user_id)
    {
        $query = $this->getEntityManager()
            ->createQuery(
                'SELECT T 
                    FROM App\Entity\UserManagement\Tracking T 
                    JOIN T.user u
                    WHERE u.id = :id
                    ORDER BY T.created DESC'
            )
            ->setParameter('id', $user_id)
            ->setMaxResults(1);
        try {
            $entity = $query->getOneOrNullResult();
        } catch (\Doctrine\ORM\NoResultException $e) {
            $entity = null;
        }
    }

    public function getByUser(User $user, int $page = 1, int $maxperpage = 3000)
    {
        $query = $this->getEntityManager()
            ->createQuery(
                'SELECT T FROM App\Entity\UserManagement\Tracking T JOIN T.user u WHERE u.id = :user AND T.ipRequest != :ipRequest ORDER BY T.created DESC'
            )
            ->setParameters(['user' => $user, 'ipRequest' => '127.0.0.1'])
            ->setFirstResult(($page - 1) * $maxperpage)
            ->setMaxResults($maxperpage);

        try {
            $entities = $query->getResult();
        } catch (\Doctrine\ORM\NoResultException $e) {
            $entities = null;
        }

        return $entities;
    }

    public function getTotalByUser(User $user)
    {
        $aResultTotal = $this->getEntityManager()
            ->createQuery('SELECT COUNT(T) FROM App\Entity\UserManagement\Tracking T JOIN T.user u WHERE u.id = :user ')
            ->setParameter('user', $user)
            ->setMaxResults(1)
            ->getSingleScalarResult();

        return $aResultTotal;
    }

    public function getFilterByPage(int $page = 1, int $maxperpage = 8, string $filter, string $filter2)
    {
        $query = $this->getEntityManager()
            ->createQuery(
                'SELECT T FROM App\Entity\UserManagement\Tracking T WHERE T.pathInfo LIKE :filter AND T.queryRequest LIKE :filter2 ORDER BY T.created DESC'
            )
            ->setParameter('filter', "%{$filter}%")
            ->setParameter('filter2', "%{$filter2}%")
            ->setFirstResult(($page - 1) * $maxperpage)
            ->setMaxResults($maxperpage);

        try {
            $entities = $query->getResult();
        } catch (\Doctrine\ORM\NoResultException $e) {
            $entities = null;
        }

        return $entities;
    }
}

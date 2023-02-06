<?php

namespace App\Repository\TemplateManagement;

use App\Entity\LovManagement\Scope;
use App\Entity\TemplateManagement\Variable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Variable|null find($id, $lockMode = null, $lockVersion = null)
 * @method Variable|null findOneBy(array $criteria, array $orderBy = null)
 * @method Variable[]    findAll()
 * @method Variable[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VariableRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Variable::class);
    }

    /**
    * findEquivalencesGlobales
    * @return Variable[] Returns an array of Variable objects
    */
    public function findEquivalencesGlobales()
    {
        return $this->createQueryBuilder('v')
            ->join('v.scope', 's')
            ->where('v.isDeleted = false')
            ->andWhere('v.isValid = true')
            ->andWhere('s.code = :code')
            ->setParameter('code', Scope::GLOBALE)
            ->orderBy('v.label', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
    * findEquivalencesSystemes
    * @return Variable[] Returns an array of Variable objects
    */
    public function findEquivalencesSystemes()
    {
        return $this->createQueryBuilder('v')
            ->join('v.scope', 's')
            ->where('v.isDeleted = false')
            ->andWhere('v.isValid = true')
            ->andWhere('s.code = :code')
            ->setParameter('code', Scope::SYETEME)
            ->orderBy('v.label', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
    * findEquivalencesLocales
    * @param boolean $isValid = true
    * @return Variable[] Returns an array of Variable objects
    */
    public function findEquivalencesLocales($isValid = true)
    {
        return $this->createQueryBuilder('v')
            ->join('v.scope', 's')
            ->where('v.isDeleted = false')
            ->andWhere('v.isValid = :isValid')
            ->andWhere('s.code = :code')
            ->setParameters(['code' => Scope::LOCALE, 'isValid' => $isValid])
            ->orderBy('v.balise', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
    
    /**
    * findEquivalencesSystemesAndGlobales
    * @return Variable[] Returns an array of Variable objects
    */
    public function findEquivalencesSystemesAndGlobales()
    {
        return $this->createQueryBuilder('v')
            ->join('v.scope', 's')
            ->where('v.isDeleted = false')
            ->andWhere('v.isValid = true')
            ->andWhere('s.code IN (:codes)')
            ->setParameter('codes', [Scope::GLOBALE, Scope::SYETEME] )
            ->orderBy('v.label', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /*
    public function findOneBySomeField($value): ?Variable
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

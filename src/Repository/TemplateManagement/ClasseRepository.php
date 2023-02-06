<?php

namespace App\Repository\TemplateManagement;

use App\Entity\LovManagement\Scope;
use App\Entity\TemplateManagement\Classe;
use App\Entity\TemplateManagement\Section;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ClasseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Classe::class);
    }

    public function findBySection(Section $section)
    {
        return $this->createQueryBuilder('c')
            ->join('c.variables', 'v')
            ->join('c.sections', 'sc')        	            
            ->where('c.isValid = true')
            ->andWhere('v.isValid = true')
            ->andWhere('sc.id = :section')
            ->setParameters([
            		'section' => $section->getId()
            	])	
            ->orderBy('c.title', 'ASC') 		            	            
            ->getQuery()
            ->getResult();
        ;
    }

    public function findByGlobale()
    {
        return $this->createQueryBuilder('c')
            ->join('c.variables', 'v')
            ->join('v.scope', 'sp')	        
            ->where('sp.code IN (:codes)')
            ->andWhere('c.isValid = true')
            ->andWhere('v.isValid = true')
            ->setParameters([
            		'codes' => [Scope::GLOBALE]
            	])	            	            
            ->orderBy('c.title', 'ASC')
            ->getQuery()
            ->getResult();
        ;	
    }    

    public function findBySysteme()
    {
        return $this->createQueryBuilder('c')
            ->join('c.variables', 'v')
            ->join('v.scope', 'sp')         
            ->where('sp.code IN (:codes)')
            ->andWhere('c.isValid = true')
            ->andWhere('v.isValid = true')
            ->setParameters([
                    'codes' => [Scope::SYETEME]
                ])                              
            ->orderBy('c.title', 'ASC')
            ->getQuery()
            ->getResult();
        ;   
    }       
}

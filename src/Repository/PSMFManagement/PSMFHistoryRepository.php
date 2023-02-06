<?php

namespace App\Repository\PSMFManagement;

use App\Entity\PSMFManagement\PSMF;
use App\Entity\PSMFManagement\PSMFHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use DateInterval;
use DateTime;
use DateTimeInterface;

/**
 * @method PSMFHistory|null find($id, $lockMode = null, $lockVersion = null)
 * @method PSMFHistory|null findOneBy(array $criteria, array $orderBy = null)
 * @method PSMFHistory[]    findAll()
 * @method PSMFHistory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PSMFHistoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PSMFHistory::class);
    }

    /**
     * @return PSMFHistory[] Returns an array of PSMFHistory objects
     */
    public function findByVariableLastModification(PSMF $pSMF)
    {
        if ($pSMF->getLastVersion()) {
            $qb = $this->createQueryBuilder('ph')
                ->join('ph.psmfs', 'p')
                ->andWhere('p.id = :id')
                ->andWhere('ph.createDate > :createDate')
                ->andWhere('ph.correspondanceLocale IS NOT NULL OR ph.correspondanceGlobale IS NOT NULL OR ph.pvuser IS NOT NULL OR ph.client IS NOT NULL')
                ->setParameters([
                        'id' => $pSMF->getId(),
                        'createDate' => $pSMF->getLastVersion()->getPublicationDate() 
                    ])
                ->orderBy('ph.createDate', 'DESC'); 
        } else {
            $qb = $this->createQueryBuilder('ph')
                ->join('ph.psmfs', 'p')
                ->andWhere('p.id = :id')
                ->andWhere('ph.correspondanceLocale IS NOT NULL OR ph.correspondanceGlobale IS NOT NULL OR ph.pvuser IS NOT NULL OR ph.client IS NOT NULL')
                ->setParameter('id', $pSMF->getId())
                ->orderBy('ph.createDate', 'DESC');        
        }

        return $qb->getQuery()->getResult();
    }

    public function findByVariableLastWeekModification(PSMF $pSMF)
    {
        $lastweek = new DateTime('now');
        $lastweek->sub(new DateInterval('P7D'));
        $qb = $this->createQueryBuilder('ph')
            ->join('ph.psmfs', 'p')
            ->where('p.id = :id')
            ->andWhere('ph.correspondanceLocale IS NOT NULL OR ph.correspondanceGlobale IS NOT NULL OR ph.pvuser IS NOT NULL OR ph.client IS NOT NULL')
            ->andWhere('ph.createDate > :lastweek')
            ->setParameters(['id'=>$pSMF->getId(), 'lastweek' => $lastweek])
            ->orderBy('ph.createDate', 'DESC');        
        return $qb->getQuery()->getResult();
    }

    /**
     * @return PSMFHistory[] Returns an array of PSMFHistory objects
     */
    public function findBySectionLastModification(PSMF $pSMF)
    {
        if ($pSMF->getLastVersion()) {
            $qb = $this->createQueryBuilder('ph')
                ->andWhere('ph.createDate > :createDate')
                ->andWhere('ph.section IS NOT NULL OR ph.variable IS NOT NULL')
                ->setParameters([
                        'createDate' => $pSMF->getLastVersion()->getPublicationDate() 
                    ])
                ->orderBy('ph.createDate', 'DESC'); 
        } else {
            $qb = $this->createQueryBuilder('ph')
                ->andWhere('ph.section IS NOT NULL OR ph.variable IS NOT NULL')
                ->orderBy('ph.createDate', 'DESC');        
        }

        return $qb->getQuery()->getResult();
    }

    public function findBySectionLastWeekModification(PSMF $pSMF)
    {
        $lastweek = new DateTime('now');
        $lastweek->sub(new DateInterval('P7D'));
        $qb = $this->createQueryBuilder('ph')
                ->where('ph.section IS NOT NULL OR ph.variable IS NOT NULL')
                ->andWhere('ph.createDate > :lastweek')
                ->setParameters(['lastweek' => $lastweek])
                ->orderBy('ph.createDate', 'DESC');        

        return $qb->getQuery()->getResult();
    }


    /**
     * get total By VariableGlobaleModification
     */             
    public function getTotalByVariableGlobaleModification()
    {
        $aResultTotal = $this->getEntityManager()
            ->createQuery(
                'SELECT COUNT(ph) FROM App\Entity\PSMFManagement\PSMFHistory ph WHERE ph.correspondanceGlobale IS NOT NULL ORDER BY ph.createDate DESC')
            ->setMaxResults(1)
            ->getSingleScalarResult();

        return $aResultTotal;
    }

    public function findByVariableGlobaleModification(int $page = 1, int $maxperpage = 10)
    {
        $query = $this->getEntityManager()
            ->createQuery(
                'SELECT ph FROM App\Entity\PSMFManagement\PSMFHistory ph WHERE ph.correspondanceGlobale IS NOT NULL ORDER BY ph.createDate DESC'
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

    /**
     * get total By Section Modification
     */             
    public function getTotalBySectionModification()
    {
        $aResultTotal = $this->getEntityManager()
            ->createQuery(
                'SELECT COUNT(ph) FROM App\Entity\PSMFManagement\PSMFHistory ph WHERE ph.section IS NOT NULL ORDER BY ph.createDate DESC')
            ->setMaxResults(1)
            ->getSingleScalarResult();

        return $aResultTotal;
    }
        
    /**
     * find By Section Modification, page by page
     */
    public function findBySectionModification(int $page = 1, int $maxperpage = 10)
    {
        $query = $this->getEntityManager()
            ->createQuery(
                'SELECT ph FROM App\Entity\PSMFManagement\PSMFHistory ph WHERE ph.section IS NOT NULL ORDER BY ph.createDate DESC'
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

    /**
     * find All By Section Modification
     */
    public function findAllBySectionModification()
    {
        $queryBuilder = $this->createQueryBuilder('ph')
            ->select('ph')
            ->where('ph.section IS NOT NULL')
            ->orderBy('ph.createDate', 'DESC');

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

    /**
     * @return PSMFHistory[] Returns an array of PSMFHistory objects
     */
    public function findByVariableArchiveModification(PSMF $pSMF, DateTimeInterface $dateFin, ?DateTimeInterface $dateDebut=null)
    {
        if ($dateDebut) {
            $qb = $this->createQueryBuilder('ph')
                ->join('ph.psmfs', 'p')
                ->andWhere('p.id = :id')
                ->andWhere('ph.createDate > :dateDebut')
                ->andWhere('ph.createDate < :dateFin')
                ->andWhere('ph.correspondanceLocale IS NOT NULL OR ph.correspondanceGlobale IS NOT NULL OR ph.pvuser IS NOT NULL OR ph.client IS NOT NULL')
                ->setParameters([
                        'id' => $pSMF->getId(),
                        'dateDebut' => $dateDebut, 
                        'dateFin' => $dateFin, 
                    ])
                ->orderBy('ph.createDate', 'DESC'); 
        } else {
            $qb = $this->createQueryBuilder('ph')
                ->join('ph.psmfs', 'p')
                ->andWhere('p.id = :id')
                ->andWhere('ph.createDate < :dateFin')
                ->andWhere('ph.correspondanceLocale IS NOT NULL OR ph.correspondanceGlobale IS NOT NULL OR ph.pvuser IS NOT NULL OR ph.client IS NOT NULL')
                ->setParameters([
                    'id' => $pSMF->getId(),
                    'dateFin' => $dateFin, 
                ])
                ->orderBy('ph.createDate', 'DESC');        
        }

        return $qb->getQuery()->getResult();
    }
    
    /**
     * @return PSMFHistory[] Returns an array of PSMFHistory objects
     */
    public function findBySectionArchiveModification(PSMF $pSMF, DateTimeInterface $dateFin, ?DateTimeInterface $dateDebut=null)
    {
        if ($dateDebut) {
            $qb = $this->createQueryBuilder('ph')
                ->andWhere('ph.createDate > :dateDebut')
                ->andWhere('ph.createDate < :dateFin')
                ->andWhere('ph.section IS NOT NULL OR ph.variable IS NOT NULL')
                ->setParameters([
                        'dateDebut' => $dateDebut, 
                        'dateFin' => $dateFin,  
                    ])
                ->orderBy('ph.createDate', 'DESC'); 
        } else {
            $qb = $this->createQueryBuilder('ph')
                ->andWhere('ph.createDate < :dateFin')
                ->andWhere('ph.section IS NOT NULL OR ph.variable IS NOT NULL')
                ->setParameters([
                    'dateFin' => $dateFin, 
                ])                
                ->orderBy('ph.createDate', 'DESC');        
        }

        return $qb->getQuery()->getResult();
    }  

    /**
    * HISTORIQUE_VESRION_MAIN_BODY_MOINS_5ANS
    */
    public function findByHistoryVersionMainBodyMoins5Ans(PSMF $pSMF) {
        $moins5Ans = new DateTime('now');
        $moins5Ans->sub(new DateInterval('P5Y'));
        $qb = $this->createQueryBuilder('ph')
            ->join('ph.psmfs', 'p')
            ->andWhere('p.id = :id')
            ->andWhere('ph.createDate > :moins5Ans')
            ->andWhere('ph.correspondanceGlobale IS NOT NULL OR ph.pvuser IS NOT NULL OR ph.client IS NOT NULL')
            ->setParameters([
                    'id' => $pSMF->getId(),
                    'moins5Ans' => $moins5Ans
                ])
            ->addOrderBy('ph.correspondanceGlobale') 
            ->addOrderBy('ph.createDate', 'DESC');   
        return $qb->getQuery()->getResult();     
    }

    public function findByHistoryVersionMainBodyMoins5AnsSection(PSMF $pSMF) {
        $moins5Ans = new DateTime('now');
        $moins5Ans->sub(new DateInterval('P5Y'));
        $qb = $this->createQueryBuilder('ph')
            ->join('ph.section', 's')
            ->andWhere('ph.createDate > :moins5Ans')
            ->andWhere('ph.section IS NOT NULL OR ph.variable IS NOT NULL')
            ->andWhere('s.isAnnexe = :isAnnexe')
            ->setParameters([
                    'moins5Ans' => $moins5Ans,
                    'isAnnexe' => false
                ])
            ->addOrderBy('ph.section')
            ->addOrderBy('ph.createDate', 'DESC');  
        return $qb->getQuery()->getResult();     
    }

    /**
    * HISTORIQUE_VESRION_PSMF_ANNEXES_MOINS_5ANS
    */
    public function findByHistoryVersionPSMFAnnexesMoins5Ans(PSMF $pSMF) {
        $moins5Ans = new DateTime('now');
        $moins5Ans->sub(new DateInterval('P5Y'));
        $qb = $this->createQueryBuilder('ph')
            ->join('ph.psmfs', 'p')
            ->andWhere('p.id = :id')
            ->andWhere('ph.createDate > :moins5Ans')
            ->andWhere('ph.correspondanceLocale IS NOT NULL')
            ->setParameters([
                    'id' => $pSMF->getId(),
                    'moins5Ans' => $moins5Ans
                ])
            ->addOrderBy('ph.correspondanceLocale', 'DESC')
            ->addOrderBy('ph.createDate', 'DESC');   
        return $qb->getQuery()->getResult();    
    }

    public function findByHistoryVersionPSMFAnnexesMoins5AnsSection(PSMF $pSMF) {
        $moins5Ans = new DateTime('now');
        $moins5Ans->sub(new DateInterval('P5Y'));
        $qb = $this->createQueryBuilder('ph')
            ->join('ph.section', 's')
            ->andWhere('ph.createDate > :moins5Ans')
            ->andWhere('ph.section IS NOT NULL OR ph.variable IS NOT NULL')
            ->andWhere('s.isAnnexe = :isAnnexe')
            ->setParameters([
                    'moins5Ans' => $moins5Ans,
                    'isAnnexe' => true
                ])
            ->addOrderBy('ph.section')
            ->addOrderBy('ph.createDate', 'DESC');  
        return $qb->getQuery()->getResult();     
    }

    /*
    public function findOneBySomeField($value): ?PSMFHistory
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

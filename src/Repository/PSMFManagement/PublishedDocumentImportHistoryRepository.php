<?php

namespace App\Repository\PSMFManagement;

use App\Entity\PSMFManagement\PublishedDocumentImportHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PublishedDocumentImportHistory>
 *
 * @method PublishedDocumentImportHistory|null find($id, $lockMode = null, $lockVersion = null)
 * @method PublishedDocumentImportHistory|null findOneBy(array $criteria, array $orderBy = null)
 * @method PublishedDocumentImportHistory[]    findAll()
 * @method PublishedDocumentImportHistory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PublishedDocumentImportHistoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PublishedDocumentImportHistory::class);
    }

    public function add(PublishedDocumentImportHistory $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(PublishedDocumentImportHistory $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return PublishedDocumentImportHistory[] Returns an array of PublishedDocumentImportHistory objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?PublishedDocumentImportHistory
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}

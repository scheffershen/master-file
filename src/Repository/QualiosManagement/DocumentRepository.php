<?php

namespace App\Repository\QualiosManagement;

use App\Entity\QualiosManagement\Document;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Document>
 *
 * @method Document|null find($id, $lockMode = null, $lockVersion = null)
 * @method Document|null findOneBy(array $criteria, array $orderBy = null)
 * @method Document[]    findAll()
 * @method Document[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DocumentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Document::class);
    }

    public function findAll()
    {
        $queryBuilder = $this->createQueryBuilder('D')
            ->select('D')
            ->orderBy('D.validityDate', 'DESC');

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

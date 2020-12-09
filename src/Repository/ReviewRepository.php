<?php

namespace App\Repository;

use App\Entity\Review;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Review|null find($id, $lockMode = null, $lockVersion = null)
 * @method Review|null findOneBy(array $criteria, array $orderBy = null)
 * @method Review[]    findAll()
 * @method Review[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReviewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Review::class);
    }

    public function findAllPublishedOrderedByNewest(){
//        $this->createQueryBuilder()
//            ->addCriteria(self::createNonDeletedComments());
    }

    public function findAllWithSearch(?string $term)
    {
        $queryBuilder = $this->createQueryBuilder('review');

        if ($term) {
            $queryBuilder->andWhere('review.movieTitle LIKE :term')
                ->setParameter('term', '%' . $term . '%')
            ;
        }

        return $queryBuilder
            ->getQuery()
            ->getResult();
    }

    /**
     * The only static methods we should have in the repository
     * Needs to be static so we can use it inside article. That's because Entity classes don't have access to services.
     * @return Criteria
     */
    public static function createNonDeletedComments(): Criteria
    {
        return Criteria::create()
            ->andWhere(Criteria::expr()->eq('isDeleted', false))
            ->orderBy(['date' => 'DESC'])
        ;
    }

    // /**
    //  * @return Review[] Returns an array of Review objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Review
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

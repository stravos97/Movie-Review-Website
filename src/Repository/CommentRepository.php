<?php

namespace App\Repository;

use App\Entity\Comment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Comment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Comment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Comment[]    findAll()
 * @method Comment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comment::class);
    }

    /**
     * Method currently broken, can't search for more than one term
     * addSelect makes sure we don't have more than one query when we are making a join.
     * If the page has a lot of queries, because doctrine is making extra queries accross a relationship, join over that relationship and
     * use addSelect, to fetch all the data you need at once
     * @param string|null $term
     */
    public function getWithSearch(?string $term): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('comment')
            ->innerJoin('comment.movieID', 'review')
            ->innerJoin('comment.userID', 'user')
            ->addSelect('review', 'user')
        ;

        if ($term) {
            $queryBuilder->andWhere('comment.commentBody LIKE :term OR review.movieTitle LIKE :term OR user.firstName LIKE :term')
                ->setParameter('term', '%' . $term . '%')
            ;
        }

        return $queryBuilder;
    }

    // /**
    //  * @return Comment[] Returns an array of Comment objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Comment
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}


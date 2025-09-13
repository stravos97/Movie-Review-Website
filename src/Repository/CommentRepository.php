<?php

namespace App\Repository;

use App\Entity\Comment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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

    public function forReview(int $reviewId, int $limit = 50, int $offset = 0): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('IDENTITY(c.movieID) = :rid')
            ->setParameter('rid', $reviewId)
            ->orderBy('c.date', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function recentByUser(int $userId, int $limit = 20, int $offset = 0): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('IDENTITY(c.user) = :uid')
            ->setParameter('uid', $userId)
            ->orderBy('c.date', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function countForReview(int $reviewId): int
    {
        return (int) $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->andWhere('IDENTITY(c.movieID) = :rid')
            ->setParameter('rid', $reviewId)
            ->getQuery()
            ->getSingleScalarResult();
    }
}

<?php

namespace App\Repository;

use App\Entity\Review;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\ParameterType;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
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

    public function search(string $query, int $limit = 20, int $offset = 0): array
    {
        $qb = $this->createQueryBuilder('r');
        $qb->andWhere($qb->expr()->orX(
                'r.movieTitle LIKE :q',
                'r.summary LIKE :q',
                'r.message_body LIKE :q',
                'r.director LIKE :q'
            ))
            ->setParameter('q', '%'.$query.'%')
            ->orderBy('r.date', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }

    public function filterByRating(?int $min = null, ?int $max = null, int $limit = 20, int $offset = 0): array
    {
        $qb = $this->createQueryBuilder('r');
        if ($min !== null) {
            $qb->andWhere('r.rating >= :min')->setParameter('min', $min);
        }
        if ($max !== null) {
            $qb->andWhere('r.rating <= :max')->setParameter('max', $max);
        }
        $qb->orderBy('r.date', 'DESC')
           ->setFirstResult($offset)
           ->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }

    public function byUser(int $userId, int $limit = 20, int $offset = 0): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('IDENTITY(r.user) = :uid')
            ->setParameter('uid', $userId)
            ->orderBy('r.date', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function byGenre(?string $genre, int $limit = 20, int $offset = 0): array
    {
        $qb = $this->createQueryBuilder('r');
        if ($genre) {
            $qb->andWhere('r.genre = :genre')->setParameter('genre', $genre);
        }
        $qb->orderBy('r.date', 'DESC')
           ->setFirstResult($offset)
           ->setMaxResults($limit);
        return $qb->getQuery()->getResult();
    }

    public function byYear(?int $year, int $limit = 20, int $offset = 0): array
    {
        $qb = $this->createQueryBuilder('r');
        if ($year !== null) {
            $qb->andWhere('r.releaseYear = :year')->setParameter('year', $year);
        }
        $qb->orderBy('r.date', 'DESC')
           ->setFirstResult($offset)
           ->setMaxResults($limit);
        return $qb->getQuery()->getResult();
    }

    public function mostViewed(int $limit = 10): array
    {
        return $this->createQueryBuilder('r')
            ->orderBy('r.viewCount', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function reported(bool $onlyReported = true, int $limit = 20, int $offset = 0): array
    {
        $qb = $this->createQueryBuilder('r');
        if ($onlyReported) {
            $qb->andWhere('r.reported = 1');
        } else {
            $qb->andWhere('r.reported = 0 OR r.reported IS NULL');
        }
        $qb->orderBy('r.date', 'DESC')
           ->setFirstResult($offset)
           ->setMaxResults($limit);
        return $qb->getQuery()->getResult();
    }

    public function recent(int $limit = 10): array
    {
        return $this->createQueryBuilder('r')
            ->orderBy('r.date', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Fulltext search using MySQL MATCH...AGAINST on the fulltext index.
     * Returns Review entities ordered by relevance (score desc).
     */
    public function searchFullText(string $query, int $limit = 20, int $offset = 0): array
    {
        $em = $this->getEntityManager();
        $rsm = new ResultSetMappingBuilder($em);
        $rsm->addRootEntityFromClassMetadata(Review::class, 'r');

        $sql = 'SELECT r.*, MATCH (r.movie_title, r.summary, r.review_body, r.director) '
             . 'AGAINST (:q IN NATURAL LANGUAGE MODE) AS score '
             . 'FROM reviews r '
             . 'WHERE MATCH (r.movie_title, r.summary, r.review_body, r.director) '
             . 'AGAINST (:q IN NATURAL LANGUAGE MODE) '
             . 'ORDER BY score DESC '
             . 'LIMIT :offset, :limit';

        $native = $em->createNativeQuery($sql, $rsm);
        $native->setParameter('q', $query);
        $native->setParameter('offset', $offset, ParameterType::INTEGER);
        $native->setParameter('limit', $limit, ParameterType::INTEGER);

        // Returns an array of Review entities (score column ignored)
        return array_map(function ($row) {
            // When only root entity is mapped, Doctrine returns entities directly
            return $row;
        }, (array) $native->getResult());
    }
}

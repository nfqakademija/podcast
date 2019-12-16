<?php

namespace App\Repository;

use App\Entity\Comment;
use App\Entity\Podcast;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

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
     * @param Podcast $podcast
     * @return Comment[] Returns an array of Comment objects
     */
    public function getAllCommentsByPodcast(Podcast $podcast)
    {
        return $this->createQueryBuilder('c')
            ->innerJoin('c.user', 'u')
            ->addSelect('u')
            ->andWhere('c.podcast = :podcast')
            ->setParameter('podcast', $podcast)
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }
}

<?php

namespace App\Repository;

use App\Entity\User;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Exception;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * @return User[]|null
     * @throws Exception
     */
    public function getAllUsersWithTagsAndDailyPodcasts(): ?array
    {
        return $this->createQueryBuilder('u')
            ->innerJoin('u.tags', 't')
            ->innerJoin('t.podcasts', 'p')
            ->andWhere('p.createdAt >= :date')
            ->andWhere('u.isSubscriber = true')
            ->setParameter('date', new DateTime(date("Y-m-d")))
            ->addSelect('t', 'p')
            ->orderBy('u.id')
            ->getQuery()
            ->getResult();
    }
}

<?php

namespace App\Repository;

use App\Entity\Podcast;
use App\Entity\Source;
use App\Entity\Tag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @method Podcast|null find($id, $lockMode = null, $lockVersion = null)
 * @method Podcast|null findOneBy(array $criteria, array $orderBy = null)
 * @method Podcast[]    findAll()
 * @method Podcast[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PodcastRepository extends ServiceEntityRepository
{
    private $paginator;

    public function __construct(ManagerRegistry $registry, PaginatorInterface $paginator)
    {
        parent::__construct($registry, Podcast::class);
        $this->paginator = $paginator;
    }

    public function getAllPodcastsPaginated($page)
    {
        $qb = $this->createQueryBuilder('p')
            ->orderBy('p.publishedAt', 'DESC')
            ->getQuery();

        return $this->paginator->paginate($qb, $page, 10);
    }

    public function findAllPaginatedPodcastsBySource(Source $source, $page)
    {
        $qb = $this->createQueryBuilder('p')
            ->andWhere('p.source =:source')
            ->setParameter('source', $source)
            ->orderBy('p.publishedAt', 'DESC')
            ->getQuery();

        return $this->paginator->paginate($qb, $page, 10);
    }

    public function findAllPaginatedPodcastsByTag(Tag $tag, $page)
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.tags', 't')
            ->andWhere('t.tag = :tag')
            ->setParameter('tag', $tag->getTag())
            ->orderBy('p.publishedAt', 'DESC')
            ->getQuery();

        return $this->paginator->paginate($qb, $page, 10);
    }

    public function searchPodcasts($query, $page)
    {
        $searchTerms = explode(' ', $query);
        $qb = $this->createQueryBuilder('p');

        foreach ($searchTerms as $key => $term) {
            $qb->orWhere('p.title LIKE :p' . $key)
                ->orWhere('p.description LIKE :p' . $key)
                ->setParameter('p'.$key, '%'.trim($term).'%');
        }

        $query = $qb
            ->orderBy('p.publishedAt', 'DESC')
            ->getQuery();

        return $this->paginator->paginate($query, $page, 10);
    }
}

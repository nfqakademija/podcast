<?php

namespace App\Repository;

use App\Entity\Podcast;
use App\Entity\Source;
use App\Entity\Tag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;
use Exception;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Validator\Constraints\Date;

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
        $query = $this->createQueryBuilder('p')
            ->orderBy('p.publishedAt', 'DESC')
            ->getQuery();

        return $this->paginator->paginate($query, $page, 10);
    }

    public function findAllPaginatedPodcastsBySource(Source $source, $page)
    {
        $query = $this->createQueryBuilder('p')
            ->andWhere('p.source =:source')
            ->setParameter('source', $source)
            ->orderBy('p.publishedAt', 'DESC')
            ->getQuery();

        return $this->paginator->paginate($query, $page, 10);
    }

    public function findAllPaginatedPodcastsByTag(Tag $tag, $page)
    {
        $query = $this->createQueryBuilder('p')
            ->leftJoin('p.tags', 't')
            ->addSelect('t')
            ->andWhere('t.tag = :tag')
            ->setParameter('tag', $tag->getTag())
            ->orderBy('p.publishedAt', 'DESC')
            ->getQuery();

        return $this->paginator->paginate($query, $page, 10);
    }

    public function searchPodcasts($searchString, $page)
    {
        $qb = $this->getSearchResultsQueryBuilder($searchString);

        $query = $qb
            ->orderBy('p.publishedAt', 'DESC')
            ->getQuery();

        return $this->paginator->paginate($query, $page, 10);
    }

    public function getSearchResultsCount(string $searchString)
    {
        return $this->getSearchResultsQueryBuilder($searchString)
            ->select('count(p.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @return Podcast[]|null
     * @throws Exception
     */
    public function findAllTodaysNewPodcasts(): ?array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.createdAt >= :date')
            ->setParameter('date', new \DateTime(date("Y-m-d")))
            ->orderBy('p.title', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param $searchString
     * @return QueryBuilder
     */
    private function getSearchResultsQueryBuilder(string $searchString): QueryBuilder
    {
        $searchTerms = explode(',', $searchString);
        $qb = $this->createQueryBuilder('p');

        foreach ($searchTerms as $key => $term) {
            $qb->orWhere('p.title LIKE :p' . $key)
                ->orWhere('p.description LIKE :p' . $key)
                ->setParameter('p' . $key, '%' . trim($term) . '%');
        }
        return $qb;
    }

    public function getPodcastsCountByVideoType(): int
    {
        return $this->createQueryBuilder('p')
            ->select('count(p.id)')
            ->where('p.type = :youtube')
            ->setParameter('youtube', 'Youtube')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getPodcastsCountByAudioType(): int
    {
        return $this->createQueryBuilder('p')
            ->select('count(p.id)')
            ->where('p.type = :audio')
            ->setParameter('audio', 'Audio')
            ->getQuery()
            ->getSingleScalarResult();
    }
}

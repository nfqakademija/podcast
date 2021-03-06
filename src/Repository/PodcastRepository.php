<?php

namespace App\Repository;

use App\Entity\Podcast;
use App\Entity\Source;
use App\Entity\Tag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;
use Exception;
use Knp\Component\Pager\Pagination\PaginationInterface;
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

    /**
     * @param $page
     * @return PaginationInterface
     */
    public function getAllPodcastsPaginated($page)
    {
        $query = $this->createQueryBuilder('p')
            ->leftJoin('p.source', 's')
            ->leftJoin('p.likesByUser', 'l')
            ->addSelect('s', 'l')
            ->orderBy('p.publishedAt', 'DESC')
            ->getQuery();

        return $this->paginator->paginate($query, $page, 10);
    }

    /**
     * @param Source $source
     * @param $page
     * @return PaginationInterface
     */
    public function findAllPaginatedPodcastsBySource(Source $source, $page)
    {
        $query = $this->createQueryBuilder('p')
            ->leftJoin('p.likesByUser', 'l')
            ->addSelect('l')
            ->andWhere('p.source =:source')
            ->setParameter('source', $source)
            ->orderBy('p.publishedAt', 'DESC')
            ->getQuery();

        return $this->paginator->paginate($query, $page, 10);
    }

    /**
     * @param Tag $tag
     * @param $page
     * @return PaginationInterface
     */
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

    /**
     * @param $searchString
     * @param $page
     * @return PaginationInterface
     */
    public function searchPodcasts($searchString, $page)
    {
        $qb = $this->getSearchResultsQueryBuilder($searchString);

        $query = $qb
            ->leftJoin('p.likesByUser', 'l')
            ->addSelect('l')
            ->orderBy('p.publishedAt', 'DESC')
            ->getQuery();

        return $this->paginator->paginate($query, $page, 10);
    }

    /**
     * @param string $searchString
     * @return mixed
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
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
     * @param string $searchString
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

    /**
     * @param string $type
     * @return int
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function getPodcastsCountByType(string $type): int
    {
        return $this->createQueryBuilder('p')
            ->select('count(p.id)')
            ->where('p.type = :audio')
            ->setParameter('audio', $type)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param $type
     * @param $page
     * @return PaginationInterface
     */
    public function findAllPaginatedPodcastsByType($type, $page)
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.likesByUser', 'l')
            ->addSelect('l')
            ->where('p.type = :type')
            ->setParameter('type', $type)
            ->orderBy('p.publishedAt', 'DESC')
            ->getQuery();

        return $this->paginator->paginate($qb, $page, 10);
    }

    /**
     * @param int $limit
     * @return Podcast[]|null
     */
    public function findAllPodcastsByLimit(int $limit): ?array
    {
        $query = $this->createQueryBuilder('p')
            ->orderBy('p.publishedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery();

        return $query->getResult();
    }

    /**
     * @param int $podcast_id
     * @return Podcast[]|null
     */
    public function findPodcastById(int $podcast_id): ?array
    {
        $query = $this->createQueryBuilder('p')
            ->where('p.id = :podcast_id')
            ->setParameter('podcast_id', $podcast_id)
            ->getQuery();

        return $query->getResult();
    }
}

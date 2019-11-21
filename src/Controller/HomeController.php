<?php

namespace App\Controller;

use App\Entity\Podcast;
use App\Entity\Source;
use App\Entity\Tag;
use App\Repository\PodcastRepository;
use App\Repository\SourceRepository;
use App\Repository\TagRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/{page}", name="podcasts", defaults={"page":1}, requirements={"page"="\d+"})
     */
    public function front(
        SourceRepository $sourceRepository,
        PodcastRepository $podcastRepository,
        TagRepository $tagRepository,
        $page
    ) {
        return $this->render('front/pages3/posts/index.html.twig', [
            'podcasts' => $podcastRepository->getAllPodcastsPaginated($page),
            'sources' => $sourceRepository->findAll(),
            'tags' => $tagRepository->findAll()
        ]);
    }

    /**
     * @Route("podcasts/{source}/{page}", name="podcasts_by_source", defaults={"page":1})
     */
    public function showPodcastsBySource(
        SourceRepository $sourceRepository,
        PodcastRepository $podcastRepository,
        TagRepository $tagRepository,
        Source $source,
        $page
    ) {
        return $this->render('front/pages3/posts/index.html.twig', [
            'podcasts' => $podcastRepository->findAllPaginatedPodcastsBySource($source, $page),
            'sources' => $sourceRepository->findAll(),
            'tags' => $tagRepository->findAll()
        ]);
    }

     /**
     * @Route("podcast/{podcast}/", name="single_podcast")
     */
    public function showPodcast(
        Podcast $podcast,
        TagRepository $tagRepository,
        SourceRepository $sourceRepository
    ) {
        return $this->render('front/pages/posts/show.html.twig', [
            'podcast' => $podcast,
            'sources' => $sourceRepository->findAll(),
            'tags' => $tagRepository->findAll()
        ]);
    }

    /**
     * @Route("tag/{tag}/{page}", name="podcasts_by_tag", defaults={"page":1})
     */
    public function showPodcastsByTag(
        Tag $tag,
        PodcastRepository $podcastRepository,
        TagRepository $tagRepository,
        SourceRepository $sourceRepository,
        $page
    ) {
        return $this->render('front/pages/posts/index.html.twig', [
            'podcasts' => $podcastRepository->findAllPaginatedPodcastsByTag($tag, $page),
            'sources' => $sourceRepository->findAll(),
            'tags' => $tagRepository->findAll()
        ]);
    }
}

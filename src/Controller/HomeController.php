<?php

namespace App\Controller;

use App\Entity\Source;
use App\Repository\PodcastRepository;
use App\Repository\SourceRepository;
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
        $page
    ) {
        return $this->render('front/pages/posts/index.html.twig', [
            'podcasts' => $podcastRepository->getAllPodcastsPaginated($page),
            'sources' => $sourceRepository->findAll()
        ]);
    }

    /**
     * @Route("podcasts/{source}/{page}", name="podcasts_by_source", defaults={"page":1})
     */
    public function showPodcastsBySource(
        SourceRepository $sourceRepository,
        PodcastRepository $podcastRepository,
        Source $source,
        $page
    ) {
        return $this->render('front/pages/posts/index.html.twig', [
            'podcasts' => $podcastRepository->findAllPaginatedPodcastsBySource($source, $page),
            'sources' => $sourceRepository->findAll()
        ]);
    }
}

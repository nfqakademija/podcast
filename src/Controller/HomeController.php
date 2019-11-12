<?php

namespace App\Controller;

use App\Repository\PodcastRepository;
use App\Repository\SourceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Knp\Component\Pager\PaginatorInterface;

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
}

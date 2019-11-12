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
     * @Route("/", name="podcasts")
     */
    public function front(
        Request $request,
        PaginatorInterface $paginator,
        SourceRepository $sourceRepository,
        PodcastRepository $podcastRepository
    ) {
        $podcasts = $podcastRepository->findAll();

        $podcasts = $paginator->paginate(
            $podcasts,
            $request->query->getInt('page', 1),
            10
        );
        return $this->render('front/pages/posts/index.html.twig', [
            'podcasts' => $podcasts,
            'sources' => $sourceRepository->findAll()
        ]);
    }
}

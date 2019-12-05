<?php

namespace App\Controller;

use App\Repository\PodcastRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use App\Service\XmlService;

class RssFeedController extends AbstractController
{
    /**
     * @Route("/rss", name="rss_feed")
     */
    public function index(PodcastRepository $podcastRepository, XmlService $xmlService)
    {
        $limit = 20;
        $podcasts = $podcastRepository->findAllPodcastsByLimit($limit);

        $response = new Response();
        $response->headers->set("Content-type", "text/xml");
        $response->setContent($xmlService->generate($podcasts));

        return $response;
    }
}

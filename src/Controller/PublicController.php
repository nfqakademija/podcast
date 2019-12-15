<?php

namespace App\Controller;

use App\Entity\Podcast;
use App\Repository\PodcastRepository;
use App\Repository\TagRepository;
use App\Service\XmlService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\SourceRepository;

class PublicController extends AbstractController
{
    private $sourceRepository;

    private $podcastRepository;

    public function __construct(SourceRepository $sourceRepository, PodcastRepository $podcastRepository)
    {
        $this->sourceRepository = $sourceRepository;
        $this->podcastRepository = $podcastRepository;
    }

    /**
     * @Route("/apie_projekta", name="about_project")
     */
    public function aboutPage()
    {
        return $this->render('front/pages/about/index.html.twig', [
            'sources' => $this->sourceRepository->findAll(),
        ]);
    }

    /**
     * @Route("/rss", name="rss_feed")
     */
    public function getRssFeed(XmlService $xmlService)
    {
        $limit = 20;
        $podcasts = $this->podcastRepository->findAllPodcastsByLimit($limit);

        $response = new Response();
        $response->headers->set("Content-type", "text/xml");
        $response->setContent($xmlService->generate($podcasts));

        return $response;
    }

    public function getNavigationBar(TagRepository $tagRepository)
    {
        return $this->render('front/layout/sidebar.html.twig', [
            'tags' => $tagRepository->getTenOldestTags(),
            'audioCount' => $this->podcastRepository->getPodcastsCountByType(Podcast::TYPES['TYPE_AUDIO']),
            'videoCount' => $this->podcastRepository->getPodcastsCountByType(Podcast::TYPES['TYPE_VIDEO']),
        ]);
    }

    public function getSourcesSection()
    {
        return $this->render('front/pages/posts/_sources_sidebar.html.twig', [
            'sources' => $this->sourceRepository->findAll()
        ]);
    }

    public function getSourcesSectionForMobile()
    {
        return $this->render('front/pages/posts/_sources_mobile.html.twig', [
            'sources' => $this->sourceRepository->findAll()
        ]);
    }
}

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
    /**
     * @var SourceRepository
     */
    private $sourceRepository;

    /**
     * @var PodcastRepository
     */
    private $podcastRepository;

    private const LIMIT__OF_RSS_PODCASTS = 20;

    public function __construct(SourceRepository $sourceRepository, PodcastRepository $podcastRepository)
    {
        $this->sourceRepository = $sourceRepository;
        $this->podcastRepository = $podcastRepository;
    }

    /**
     * @Route("/apie_projekta", name="about_project")
     * @return Response
     */
    public function aboutPage(): Response
    {
        return $this->render('front/pages/about/index.html.twig', [
            'sources' => $this->sourceRepository->findAll(),
        ]);
    }

    /**
     * @Route("/rss", name="rss_feed")
     * @param XmlService $xmlService
     * @return Response
     */
    public function getRssFeed(XmlService $xmlService): Response
    {
        $podcasts = $this->podcastRepository->findAllPodcastsByLimit(self::LIMIT__OF_RSS_PODCASTS);

        $response = new Response();
        $response->headers->set("Content-type", "text/xml");
        $response->setContent($xmlService->generate($podcasts));

        return $response;
    }

    /**
     * @param TagRepository $tagRepository
     * @return Response
     */
    public function getNavigationBar(TagRepository $tagRepository): Response
    {
        return $this->render('front/layout/sidebar.html.twig', [
            'tags' => $tagRepository->getTenOldestTags(),
            'audioCount' => $this->podcastRepository->getPodcastsCountByType(Podcast::TYPES['TYPE_AUDIO']),
            'videoCount' => $this->podcastRepository->getPodcastsCountByType(Podcast::TYPES['TYPE_VIDEO']),
        ]);
    }

    /**
     * @return Response
     */
    public function getSourcesSection(): Response
    {
        return $this->render('front/pages/podcasts/_sources_sidebar.html.twig', [
            'sources' => $this->sourceRepository->findAll()
        ]);
    }

    /**
     * @return Response
     */
    public function getSourcesSectionForMobile(): Response
    {
        return $this->render('front/pages/podcasts/_sources_mobile.html.twig', [
            'sources' => $this->sourceRepository->findAll()
        ]);
    }
}

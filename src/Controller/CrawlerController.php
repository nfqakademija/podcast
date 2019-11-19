<?php

namespace App\Controller;

use App\Entity\Podcast;
use App\Repository\SourceRepository;
use App\Service\CrawlerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class CrawlerController extends AbstractController
{
    /**
     * @Route("/crawler", name="crawler")
     */
    public function index(CrawlerService $crawlerService, SourceRepository $sourceRepository)
    {
        $sources = $sourceRepository->findBy(['sourceType' => Podcast::TYPES['TYPE_AUDIO']]);
        $podcasts = $crawlerService->scrapSites($sources);

        dd($podcasts);
    }
}

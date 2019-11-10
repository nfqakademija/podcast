<?php

namespace App\Controller;

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
        $sources = $sourceRepository->findAll();

        $podcasts = $crawlerService->scrapSites($sources);

        dd($podcasts);
    }
}

<?php

namespace App\Controller;

use App\Repository\SourceRepository;
use App\Service\YoutubeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class YoutubeApiController extends AbstractController
{
    /**
     * @Route("/youtube", name="youtube")
     * @param YoutubeService $youtubeApiService
     * @param SourceRepository $sourceRepository
     */
    public function index(YoutubeService $youtubeApiService, SourceRepository $sourceRepository)
    {
        $sources = $sourceRepository->findBy([
            'sourceType' => 'Youtube'
        ]);

        $youtubeApiService->importDataFromYoutube($sources);

        //dd($request);
    }
}

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
     */
    public function index(YoutubeService $youtubeApiService, SourceRepository $sourceRepository)
    {
        $sources = $sourceRepository->findBy([
            'sourceType' => 'Youtube'
        ]);

        $res = $youtubeApiService->importDataFromYoutube($sources);

        var_dump($res);
        return $this->render('home/index.html.twig', [
            'someVariable' => ''
        ]);
    }
}

<?php

namespace App\Controller;

use App\Entity\Podcast;
use App\Repository\PodcastRepository;
use App\Service\CrawlerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CrawlerController extends AbstractController
{
    /**
     * @var PodcastRepository
     */
    private $podcastRepository;

    public function __construct(PodcastRepository $podcastRepository)
    {
        $this->podcastRepository = $podcastRepository;
    }

    /**
     * @Route("/crawler", name="crawler")
     */
    public function index(CrawlerService $crawlerService)
    {
        $podcasts = $crawlerService->scrapSites();
        $html = "<table>";
        foreach ($podcasts as $source => $sourcePodcasts) {
            $html .= "<tr><th>$source</th></tr>";
            if (is_array($sourcePodcasts)) {
                /** @var Podcast $podcast */
                foreach ($sourcePodcasts as $podcast) {
                    $html .= "<tr><td>" . $podcast->getTitle() . "</td></tr>";
                }
            } else {
                $html .= "<tr><td>Nauju podkastu siuo metu nera</td></tr>";
            }
        }
        $html .= "</table>";

        return new Response($html);
    }
}

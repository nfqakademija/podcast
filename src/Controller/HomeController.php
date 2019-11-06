<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index()
    {
        return $this->render('home/index.html.twig', [
            'someVariable' => 'Krepšinio podcastai',
        ]);
    }

    /**
     * @Route("/test")
     */
    public function scrapSite()
    {
        $page = 1;
        $podcasts = [];

        while (true) {
            $url = 'https://basketnews.podbean.com/page/' . $page;
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $html = curl_exec($ch);
            $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($statusCode == 404) {
                break;
            }

            $crawler = new Crawler($html);

            $crawler->filter('.entry')->each(function (Crawler $node) use (&$podcasts) {
                $podcast['image'] = $node->filter("img")->attr('data-src');
                $podcast['title'] = $node->filter('h2')->text();
                $podcast['description'] = $node->filter('p')->text();
                $podcast['audio'] = $node->filter('.theme1')->attr('data-uri');
                $podcast['publication_date'] = $node->filter('.date')->text();
                $podcasts[] = $podcast;
            });

            $page++;
        };



//        $crawler = new Crawler($html);
//        $podcasts = [];
//        $crawler->filter('.entry')->each(function (Crawler $node) use (&$podcasts) {
//            $podcast['image'] = $node->filter("img")->attr('data-src');
//            $podcast['title'] = $node->filter('h2')->text();
//            $podcast['description'] = $node->filter('p')->text();
//            $podcast['audio'] = $node->filter('.theme1')->attr('data-uri');
//            $podcast['created_at'] = $node->filter('.date')->text();
//            $podcasts[] = $podcast;
//        });

        dd($podcasts);
    }
}

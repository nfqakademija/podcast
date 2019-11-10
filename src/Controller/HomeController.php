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
    //$publicationDateSelector
//        string $url,
//        string $elementSelector,
//        string $imageSelector,
//        string $titleSelector,
//        string $descriptionSelector,
//        string $audioSelector,
//
    /**
     * @Route("/test")
     */
    public function scrapSite()
    {
        $podcasts = [];

//        $elementSelector = '.playlist-item';
//        $imageSelector = null;
//        $titleSelector = '.player-title';
//        $descriptionSelector = null;
//        $audioSelector = '.playlist-item';
//        $publicationDateSelector = '.player-date';
//        $streamAttribute = 'data-src';

        $elementSelector = '.entry';
        $imageSelector = 'img';
        $titleSelector = 'h2';
        $descriptionSelector = 'p';
        $audioSelector = '.theme1';
        $publicationDateSelector = '.date';
        $streamAttribute = 'data-uri';

        $url = 'https://basketnews.podbean.com';
//        $url = 'https://www.delfi.lt/klausyk/krepsinio-zonoje/';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $html = curl_exec($ch);
//        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

//            dd($html);

        $crawler = new Crawler($html);

        $crawler->filter($elementSelector)->each(function (Crawler $node)
        use (&$podcasts, $imageSelector, $titleSelector, $descriptionSelector,
            $audioSelector, $publicationDateSelector, $streamAttribute)
        {
            if ($imageSelector) $podcast['image'] = $node->filter($imageSelector)->attr('src');
            if ($titleSelector) $podcast['title'] = $node->filter($titleSelector)->text();
            if ($descriptionSelector) $podcast['description'] = $node->filter($descriptionSelector)->text();
            if ($audioSelector) $podcast['audio'] = $node->filter($audioSelector)->attr($streamAttribute);
            if($publicationDateSelector) $podcast['publication_date'] = $node->filter($publicationDateSelector)->text();
            $podcasts[] = $podcast;
        });

//        dd($podcasts);

        $date = date('September 10 d., 2019');
        dd($date);
    }
}

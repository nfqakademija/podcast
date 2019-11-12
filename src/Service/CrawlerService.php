<?php

namespace App\Service;

use App\Entity\Podcast;
use App\Entity\Source;
use App\Repository\PodcastRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DomCrawler\Crawler;

class CrawlerService
{
    private $entityManager;

    private $podcastRepository;

    public function __construct(EntityManagerInterface $entityManager, PodcastRepository $podcastRepository)
    {
        $this->entityManager = $entityManager;
        $this->podcastRepository = $podcastRepository;
    }

    public function scrapSites(?array $sources)
    {
        $podcasts = [];

        /** @var Source $source */
        foreach ($sources as $source) {
            $url = $source->getUrl();
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $html = curl_exec($ch);
            //        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $crawler = new Crawler($html);

            $crawler->filter($source->getMainElementSelector())
                ->each(function (Crawler $node) use (&$podcasts, $source) {

                    $podcast = new Podcast();

                    if ($source->getImageSelector()) {
                        $podcast->setImage(
                            $node->filter($source->getImageSelector())->attr($source->getImageSourceAttribute())
                        );
                    }
                    if ($source->getTitleSelector()) {
                        $podcast->setTitle($node->filter($source->getTitleSelector())->text());
                    }
                    if ($source->getDescriptionSelector()) {
                        $podcast->setDescription($node->filter($source->getDescriptionSelector())->last()->text());
                    }
                    if ($source->getAudioSelector()) {
                        $podcast->setAudio(
                            $node->filter($source->getAudioSelector())->attr($source->getAudioSourceAttribute())
                        );
                    }
                    if ($source->getPublicationDateSelector()) {
                        $date = $this->formatDate($node->filter($source->getPublicationDateSelector())->text());
                        $podcast->setPublishedAt($date);
                    }

                    if (null === $this->checkIfPodcastExists($podcast)) {
                        $podcast->setCreatedAt(new \DateTime());
                        $podcast->setSource($source);
                        $this->entityManager->persist($podcast);
                        $this->entityManager->flush();
                    }
                    $podcasts[] = $podcast;
                });
        }

        return $podcasts;
    }

    private function checkIfPodcastExists(Podcast $podcast)
    {
        $podcast = $this->podcastRepository->findOneBy([
            'title' => $podcast->getTitle(),
            'publishedAt' =>$podcast->getPublishedAt()
        ]);

        return $podcast;
    }

    private function formatDate($date)
    {
        $lithuanianMonths = [
            'Sausio', 'Vasario', 'Kovo', 'Balandžio', 'Gegužės', 'Birželio',
            'Liepos', 'Rugpjūčio', 'Rugsėjo', 'Spalio', 'Lapkričio', 'Gruodžio'
        ];
        $englishMonths = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];
        $date = str_replace($lithuanianMonths, $englishMonths, $date);
        $dateArray = date_parse($date);

        if ($dateArray['year'] !== false && $dateArray['month'] !== false && $dateArray['day'] !== false) {
            $newDate = new \DateTime($dateArray['year']. '/'.$dateArray['month']. '/' . $dateArray['day']);
        } else {
            $newDate = (new \DateTime(date('Y-m-d')));
        }

        return $newDate;
    }
}

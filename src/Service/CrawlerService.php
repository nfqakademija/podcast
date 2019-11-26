<?php

namespace App\Service;

use App\Entity\Podcast;
use App\Entity\Source;
use App\Repository\PodcastRepository;
use App\Repository\SourceRepository;
use App\Repository\TagRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class CrawlerService
{
    private $entityManager;
    private $podcastRepository;
    private $tagRepository;
    private $logger;
    private $client;
    private $sourceRepository;
    private $taggingService;

    public function __construct(
        EntityManagerInterface $entityManager,
        PodcastRepository $podcastRepository,
        TagRepository $tagRepository,
        LoggerInterface $logger,
        HttpClientInterface $client,
        SourceRepository $sourceRepository,
        TaggingService $taggingService
    ) {
        $this->entityManager = $entityManager;
        $this->podcastRepository = $podcastRepository;
        $this->tagRepository = $tagRepository;
        $this->logger = $logger;
        $this->client = $client;
        $this->sourceRepository = $sourceRepository;
        $this->taggingService = $taggingService;
    }

    /**
     * Takes all Sources with Audio type and loops thru to collect and save new podcasts
     *
     * @return array|mixed
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function scrapSites()
    {
        $sources = $this->sourceRepository->findBy(['sourceType' => Podcast::TYPES['TYPE_AUDIO']]);

        foreach ($sources as $source) {
            $html = $this->getSourceHtmlCode($source);

            try {
                $podcasts[$source->getName()] = $this->createNewPodcastsFromCrawler($html, $source);
            } catch (Exception $e) {
                $this->logger->error('Something wrong with '.$source->getName() .': '.$e->getMessage());
            }
        }

        $this->entityManager->flush();

        return $podcasts;
    }

    /**
     * Http Client makes a request to given url and takes html as a content
     *
     * @param Source $source
     * @return string
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws Exception
     */
    private function getSourceHtmlCode(Source $source): string
    {
        $url = $source->getUrl();
        $response = $this->client->request('GET', $url);
        if (200 !== $response->getStatusCode()) {
            throw new Exception('Puslapis neveikia!');
        }
        $html = $response->getContent();

        return $html;
    }

    /**
     * Crawler searches for podcasts in HTML and creates a new Podcast object for each found element
     * from given css/html selectors
     *
     * @param string $html
     * @param Source $source
     * @return mixed
     */
    private function createNewPodcastsFromCrawler(string $html, Source $source)
    {
        $crawler = new Crawler($html);
        $crawler->filter($source->getMainElementSelector())
            ->each(function (Crawler $node) use (&$podcasts, $source) {
                $podcast = new Podcast();
                $podcast->setTitle($node->filter($source->getTitleSelector())->text());
                $publicationDate = $this->formatDate($node->filter($source->getPublicationDateSelector())->text());
                $podcast->setPublishedAt($publicationDate);

                // if there is no podcast with the same title and date in DB, continues setting Podcast properties
                if (null === $this->checkIfPodcastExists($podcast)) {
                    $podcast->setAudio(
                        $node->filter($source->getAudioSelector())->attr($source->getAudioSourceAttribute())
                    );

                    if ($source->getImageSelector()) {
                        $podcast->setImage(
                            $node->filter($source->getImageSelector())->attr($source->getImageSourceAttribute())
                        );
                    }
                    if ($source->getDescriptionSelector()) {
                        $podcast->setDescription($node->filter($source->getDescriptionSelector())->first()->text());
                    }
                    $podcast->setSource($source);
                    $podcast->setType(Podcast::TYPES['TYPE_AUDIO']);
                    // Checks for tags in database and if there is a match in new podcast, adds to tag to it
                    $matchedTags = $this->taggingService->findTagsInPodcast($podcast);

                    if (count($matchedTags) > 0) {
                        foreach ($matchedTags as $tag) {
                            $podcast->addTag($tag);
                        }
                    }
                    $this->entityManager->persist($podcast);
                    $podcasts[] = $podcast;
                    $this->logger->info(sprintf('Added new podcast %s', $podcast->getTitle()));
                }
            });

        return $podcasts;
    }

    /**
     * Checks if Podcast is already in database with same title and publication date
     *
     * @param Podcast $podcast
     * @return Podcast|null
     */
    private function checkIfPodcastExists(Podcast $podcast)
    {
        $podcast = $this->podcastRepository->findOneBy([
            'title' => $podcast->getTitle(),
            'publishedAt' =>$podcast->getPublishedAt()
        ]);

        return $podcast;
    }

    /**
     *
     * Tries to properly format date from lithuanian language or incorrectly formatted date string from html
     *
     * @param $date
     * @return DateTime
     * @throws Exception
     */
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
            $newDate = new DateTime($dateArray['year']. '/'.$dateArray['month']. '/' . $dateArray['day']);
        } else {
            $newDate = (new DateTime(date('Y-m-d')));
        }

        return $newDate;
    }
}

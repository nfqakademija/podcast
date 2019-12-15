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
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var PodcastRepository
     */
    private $podcastRepository;

    /**
     * @var TagRepository
     */
    private $tagRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var HttpClientInterface
     */
    private $client;

    /**
     * @var SourceRepository
     */
    private $sourceRepository;

    /**
     * @var TaggingService
     */
    private $taggingService;

    /**
     * @var MailService
     */
    private $mailService;

    public function __construct(
        EntityManagerInterface $entityManager,
        PodcastRepository $podcastRepository,
        TagRepository $tagRepository,
        LoggerInterface $logger,
        HttpClientInterface $client,
        SourceRepository $sourceRepository,
        TaggingService $taggingService,
        MailService $mailService
    ) {
        $this->entityManager = $entityManager;
        $this->podcastRepository = $podcastRepository;
        $this->tagRepository = $tagRepository;
        $this->logger = $logger;
        $this->client = $client;
        $this->sourceRepository = $sourceRepository;
        $this->taggingService = $taggingService;
        $this->mailService = $mailService;
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
        $tags = $this->tagRepository->findAll();
        $existingPodcasts = $this->podcastRepository->findAll();
        $podcasts = [];

        foreach ($sources as $source) {
            $html = $this->getSourceHtmlCode($source);

            try {
                $podcasts[$source->getName()] =
                    $this->createNewPodcastsFromCrawler($html, $source, $existingPodcasts);
            } catch (Exception $e) {
                $error = 'Something wrong with '.$source->getName() .': '.$e->getMessage();
                $this->logger->error($error);
                $this->mailService->sendCrawlerFailNotificationToAdmins($source->getName(), $error);
            }
        }

        $this->addTagsToPodcasts($podcasts, $tags);

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
        return $response->getContent();
    }

    /**
     * Crawler searches for podcasts in HTML and creates a new Podcast object for each found element
     * from given css/html selectors
     *
     * @param string $html
     * @param Source $source
     * @param array $existingPodcasts
     * @return mixed
     */
    private function createNewPodcastsFromCrawler(string $html, Source $source, array $existingPodcasts)
    {
        $crawler = new Crawler($html);
        $crawler->filter($source->getMainElementSelector())
            ->each(function (Crawler $node) use (&$podcasts, $source, $existingPodcasts) {
                $podcast = new Podcast();
                $podcast->setTitle($node->filter($source->getTitleSelector())->text());
                $publicationDate = $this->formatDate($node->filter($source->getPublicationDateSelector())->text());
                $podcast->setPublishedAt($publicationDate);

                // if there is no podcasts with the same title and date in DB, continues setting Podcast properties
                if ($this->checkIfPodcastDoNotExist($podcast, $existingPodcasts)) {
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
     * @param Podcast $newPodcast
     * @param array $existingPodcasts
     * @return bool
     */
    private function checkIfPodcastDoNotExist(Podcast $newPodcast, array $existingPodcasts): bool
    {
        /** @var Podcast $existingPodcast */
        foreach ($existingPodcasts as $existingPodcast) {
            if ($existingPodcast->getTitle() === $newPodcast->getTitle() &&
                $existingPodcast->getPublishedAt()->format('Y-m-d')
                === $newPodcast->getPublishedAt()->format('Y-m-d')) {
                return false;
            }
        }
        return true;
    }

    /**
     *
     * Tries to properly format date from lithuanian language or incorrectly formatted date string from html
     *
     * @param string $date
     * @return DateTime
     * @throws Exception
     */
    private function formatDate(string $date)
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

    /**
     * Checks for tags in database and if there is a match in new podcast, adds tag to it
     *
     * @param array $newPodcasts
     * @param array $tags
     */
    private function addTagsToPodcasts(array $newPodcasts, array $tags): void
    {
        foreach ($newPodcasts as $podcastsBySource) {
            if (is_array($podcastsBySource)) {
                foreach ($podcastsBySource as $podcast) {
                    $matchedTags = $this->taggingService->findTagsInPodcast($podcast, $tags);

                    if (count($matchedTags) > 0) {
                        foreach ($matchedTags as $tag) {
                            $podcast->addTag($tag);
                        }
                    }
                }
            }
        }
    }
}

<?php


namespace App\Service;

use App\Entity\Podcast;
use App\Entity\Source;
use App\Repository\PodcastRepository;

use App\Repository\SourceRepository;
use DateTime;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\HttpClient;
use Doctrine\ORM\EntityManagerInterface;
use Throwable;

class YoutubeService
{
    private $httpClient;
    private $entityManager;
    private $podcastRepository;
    private $sourceRepository;
    private $logger;

    private $requestUrl;
    private $apiCode;

    public function __construct(
        EntityManagerInterface $entityManager,
        PodcastRepository $podcastRepository,
        SourceRepository $sourceRepository,
        LoggerInterface $logger,
        $requestUrl,
        $apiCode
    ) {
        $this->httpClient = HttpClient::create();
        $this->entityManager = $entityManager;
        $this->podcastRepository = $podcastRepository;
        $this->sourceRepository = $sourceRepository;
        $this->logger = $logger;
        $this->requestUrl = $requestUrl;
        $this->apiCode = $apiCode;
    }

    public function importDataFromYoutube(array $listOfSources): bool
    {
        /** @var Source $source */
        foreach ($listOfSources as $source) {
            $playlist = false;
            if (preg_match('~playlist\?list=~', $source->getUrl())) {
                $content = $this->getDataFromPlaylist($source->getUrl());
                $playlist = true;
            } else {
                $content = $this->getDataFromChannel($source);
            }

            try {
                foreach ($content['items'] as $video) {
                    if ($playlist && !empty($video['snippet']['thumbnails'])) {
                        $videoId = explode('/', end($video['snippet']['thumbnails'])['url']);
                        $videoId = $videoId[sizeof($videoId)-2];
                    } else {
                        continue;
                    }
                    if (($video['snippet']['liveBroadcastContent']??'none') === 'none'
                        && !$this->isVideoExists($videoId??$video['id']['videoId'])
                    ) {
                        $podcast = new Podcast();

                        $podcast->setSource($source);
                        $podcast->setPublishedAt(new DateTime($video['snippet']['publishedAt']));
                        $podcast->setDescription($video['snippet']['description']);
                        $podcast->setTitle($video['snippet']['title']);
                        $podcast->setImage(end($video['snippet']['thumbnails'])['url']);
                        $podcast->setCreatedAt(new DateTime('now'));
                        $podcast->setVideo($videoId??$video['id']['videoId']);

                        $this->entityManager->persist($podcast);
                        $this->entityManager->flush();
                    }
                }
            } catch (Throwable $e) {
                $this->logger->error($e);
                continue;
            }
        }
        return true;
    }

    private function getDataFromPlaylist($url): array
    {
        $parts = parse_url($url);
        parse_str($parts['query'], $query);
        try {
            $response = $this->httpClient->request('GET', $this->requestUrl . 'playlistItems', [
                'query' => [
                    'part' => 'snippet',
                    'playlistId' => $query['list'],
                    'maxResults' => '50',
                    'pageToken' => '',
                    'key' => $this->apiCode
                ]
            ]);
            $content = $response->toArray();

            if ($response->getStatusCode() != 200) {
                throw new Exception($content['error']['message']);
            }
            return $content;
        } catch (Throwable $e) {
            $this->logger->error($e);
            return [];
        }
    }

    private function getDataFromChannel(Source $source): array
    {
        $channelId = $this->getChannelId($source);
        try {
            $response = $this->httpClient->request('GET', $this->requestUrl . 'search', [
                'query' => [
                    'part' => 'snippet',
                    'channelId' => $channelId,
                    'maxResults' => '50',
                    'order' => 'date',
                    'pageToken' => '',
                    'type' => 'video',
                    'key' => $this->apiCode
                ]
            ]);
            $content = $response->toArray();
            if ($response->getStatusCode() != 200) {
                throw new Exception($content['error']['message']);
            }
            return $content;
        } catch (Throwable $e) {
            $this->logger->error($e);
            return [];
        }
    }

    private function isVideoExists($videoId): bool
    {
        if (empty($this->podcastRepository->findOneBy([
            'video' => $videoId
        ]))) {
            return false;
        } else {
            return true;
        }
    }

    private function getChannelId(Source $source): string
    {
        $sourceExploded = explode('/', $source->getUrl());
        if ($sourceExploded[count($sourceExploded)-2] === 'user') {
            $channelId =  $this->getChannelIdByUser(end($sourceExploded));
            if (!empty($channelId)) {
                $source->setUrl('https://www.youtube.com/channel/'.$channelId);
                $this->entityManager->persist($source);
                $this->entityManager->flush();

                return $channelId;
            } else {
                return '';
            }
        } else {
            return end($sourceExploded);
        }
    }

    private function getChannelIdByUser(string $username): string
    {

        try {
            $response = $this->httpClient->request('GET', $this->requestUrl . 'channels', [
                'query' => [
                    'part' => 'contentDetails',
                    'forUsername' => $username,
                    'key' => $this->apiCode
                ]
            ]);

            $content = $response->toArray();

            if ($response->getStatusCode() != 200) {
                throw new Exception($content['error']['message']);
            }
            return $content['items'][0]['id'];
        } catch (Throwable $e) {
            $this->logger->error($e);
            return false;
        }
    }
}

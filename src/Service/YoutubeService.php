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
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

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

    public function importDataFromYoutube(array &$listOfSources): bool
    {
        /** @var Source $source */
        foreach ($listOfSources as $source) {
            $playlist = false;
            if (preg_match('~playlist\?list=~', $source->getUrl())) {
                $parts = parse_url($source->getUrl());
                parse_str($parts['query'], $query);
                try {
                    $response = $this->httpClient->request('GET', $this->requestUrl . 'playlistItems', [
                        'query' => [
                            'part' => 'snippet',
                            'playlistId' => $query['list'],
                            'maxResults' => '10',
                            'pageToken' => '',
                            'key' => $this->apiCode
                        ]
                    ]);
                    $playlist = true;
                } catch (TransportExceptionInterface $e) {
                    $this->logger->error($e->getMessage());
                    return false;
                }
            } else {
                $channelId = $this->getChannelId($source);
                try {
                    $response = $this->httpClient->request('GET', $this->requestUrl . 'search', [
                        'query' => [
                            'part' => 'snippet',
                            'channelId' => $channelId,
                            'maxResults' => '10',
                            'order' => 'date',
                            'pageToken' => '',
                            'type' => 'video',
                            'key' => $this->apiCode
                        ]
                    ]);
                } catch (TransportExceptionInterface $e) {
                    $this->logger->error($e->getMessage());
                    return false;
                }
            }

            try {
                $content = $response->toArray();
                if ($response->getStatusCode() === 200) {
                    foreach ($content['items'] as $video) {
                        if ((empty($video['snippet']['liveBroadcastContent'])?
                                true
                                :
                                $video['snippet']['liveBroadcastContent'] === 'none')
                            && !$this->isVideoExists((
                            empty($video['id']['videoId'])?
                                $video['id']
                                :
                                $video['id']['videoId']))
                        ) {
                            $podcast = new Podcast();

                            $podcast->setSource($source);
                            $podcast->setPublishedAt(new DateTime($video['snippet']['publishedAt']));
                            $podcast->setDescription($video['snippet']['description']);
                            $podcast->setTitle($video['snippet']['title']);
                            $podcast->setImage(end($video['snippet']['thumbnails'])['url']);
                            $podcast->setCreatedAt(new DateTime('now'));
                            if ($playlist) {
                                $videoId = explode('/', end($video['snippet']['thumbnails'])['url']);
                                $podcast->setVideo($videoId[sizeof($videoId)-2]);
                            } else {
                                $podcast->setVideo($video['id']['videoId']);
                            }
                            $this->entityManager->persist($podcast);
                            $this->entityManager->flush();
                        }
                    }
                } else {
                    $this->logger->error($content['error']['message']);
                }
            } catch (ClientExceptionInterface $e) {
                $this->logger->error($e->getMessage());
                return false;
            } catch (DecodingExceptionInterface $e) {
                $this->logger->error($e->getMessage());
                return false;
            } catch (RedirectionExceptionInterface $e) {
                $this->logger->error($e->getMessage());
                return false;
            } catch (ServerExceptionInterface $e) {
                $this->logger->error($e->getMessage());
                return false;
            } catch (TransportExceptionInterface $e) {
                $this->logger->error($e->getMessage());
                return false;
            } catch (Exception $e) {
                $this->logger->error($e->getMessage());
                return false;
            }
        }
        return true;
    }

    private function isVideoExists($videoId): bool
    {
        if (!empty($this->podcastRepository->findOneBy([
            'video' => $videoId
        ]))) {
            return true;
        } else {
            return false;
        }
    }

    private function getChannelId(Source $source): string
    {
        $sourceExploded = explode('/', $source->getUrl());
        if ($sourceExploded[count($sourceExploded)-2] === 'user') {
            $channelId =  $this->getChannelIdByUser(end($sourceExploded));
            if (!empty($channelId)) {
                $source->setUrl('https://www.youtube.com/channel/'.$channelId);
                $this->entityManager->merge($source);
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
        } catch (TransportExceptionInterface $e) {
            $this->logger->error($e->getMessage());
            return false;
        }

        try {
            if ($response->getStatusCode() === 200) {
                $content = $response->toArray();
                return $content['items'][0]['id'];
            }
        } catch (ClientExceptionInterface $e) {
            $this->logger->error($e->getMessage());
            return false;
        } catch (DecodingExceptionInterface $e) {
            $this->logger->error($e->getMessage());
            return false;
        } catch (RedirectionExceptionInterface $e) {
            $this->logger->error($e->getMessage());
            return false;
        } catch (ServerExceptionInterface $e) {
            $this->logger->error($e->getMessage());
            return false;
        } catch (TransportExceptionInterface $e) {
            $this->logger->error($e->getMessage());
            return false;
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
            return false;
        }
    }
}

<?php

namespace App\Service;

use App\Entity\Podcast;
use App\Entity\Source;
use App\Entity\Tag;
use App\Repository\PodcastRepository;
use App\Repository\SourceRepository;
use App\Repository\TagRepository;
use DateTime;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\HttpClient;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

class YoutubeService
{
    /**
     * @var HttpClientInterface
     */
    private $httpClient;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var PodcastRepository
     */
    private $podcastRepository;
    /**
     * @var SourceRepository
     */
    private $sourceRepository;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var TaggingService
     */
    private $taggingService;
    /**
     * @var TagRepository
     */
    private $tagRepository;
    /**
     * @var string
     */
    private $requestUrl;
    /**
     * @var string
     */
    private $apiCode;

    public function __construct(
        EntityManagerInterface $entityManager,
        PodcastRepository $podcastRepository,
        SourceRepository $sourceRepository,
        LoggerInterface $logger,
        TaggingService $taggingService,
        TagRepository $tagRepository,
        string $requestUrl,
        string $apiCode
    ) {
        $this->httpClient = HttpClient::create();
        $this->entityManager = $entityManager;
        $this->podcastRepository = $podcastRepository;
        $this->sourceRepository = $sourceRepository;
        $this->logger = $logger;
        $this->taggingService = $taggingService;
        $this->tagRepository = $tagRepository;
        $this->requestUrl = $requestUrl;
        $this->apiCode = $apiCode;
    }

    /**
     * @param Source[] $listOfSources
     * @return bool
     */
    public function importDataFromYoutube(array $listOfSources): bool
    {
        /** @var Tag[] $tags */
        $tags = $this->tagRepository->findAll();
        /** @var Source $source */
        foreach ($listOfSources as $source) {
            $playlist = false;
            if (preg_match('~playlist\?list=~', $source->getUrl() ?? '')) {
                $content = $this->getDataFromPlaylist($source->getUrl() ?? '');
                $playlist = true;
            } else {
                $content = $this->getDataFromChannel($source);
            }

            try {
                foreach ($content['items'] as $video) {
                    $videoId = null;
                    if ($playlist) {
                        if (!empty($video['snippet']['thumbnails'])) {
                            $videoId = explode('/', end($video['snippet']['thumbnails'])['url']);
                            $videoId = $videoId[sizeof($videoId) - 2];
                        } else {
                            continue;
                        }
                    }
                    if (($video['snippet']['liveBroadcastContent'] ?? 'none') == 'none'
                        && !$this->isVideoExists($videoId ?? $video['id']['videoId'])
                    ) {
                        $podcast = new Podcast();

                        $podcast->setSource($source);
                        $podcast->setPublishedAt(new DateTime($video['snippet']['publishedAt']));
                        $podcast->setDescription($video['snippet']['description']);
                        $podcast->setTitle($video['snippet']['title']);
                        $podcast->setImage(end($video['snippet']['thumbnails'])['url']);
                        $podcast->setCreatedAt(new DateTime('now'));
                        $podcast->setVideo($videoId ?? $video['id']['videoId']);
                        $podcast->setType(Podcast::TYPES['TYPE_VIDEO']);

                        $this->addTags($podcast, $tags);

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

    /**
     * @param string $url
     * @return array[]
     */
    private function getDataFromPlaylist(string $url): array
    {
        $parts = parse_url($url);
        if ($parts) {
            parse_str($parts['query'], $query);
        } else {
            $query = [];
        }
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

    /**
     * @param Source $source
     * @return array[]
     */
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

    /**
     * @param string $videoId
     * @return bool
     */
    private function isVideoExists(string $videoId): bool
    {
        if (empty($this->podcastRepository->findOneBy([
            'video' => $videoId
            ]))
        ) {
            return false;
        } else {
            return true;
        }
    }

    private function getChannelId(Source $source): string
    {
        $sourceExploded = explode('/', $source->getUrl() ?? '');
        $channelId = end($sourceExploded);
        if (!$channelId) {
            return '';
        }
        if ($sourceExploded[count($sourceExploded) - 2] === 'user') {
            $channelId =  $this->getChannelIdByUser($channelId);
            if (!empty($channelId)) {
                $source->setUrl('https://www.youtube.com/channel/' . $channelId);
                $this->entityManager->persist($source);
                $this->entityManager->flush();

                return $channelId;
            } else {
                return '';
            }
        } else {
            return $channelId;
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
            return '';
        }
    }

    /**
     * @param Podcast $podcast
     * @param Tag[] $tags
     */
    private function addTags(Podcast $podcast, $tags): void
    {
        $matchedTags = $this->taggingService->findTagsInPodcast($podcast, $tags);

        if (count($matchedTags) > 0) {
            foreach ($matchedTags as $tag) {
                $podcast->addTag($tag);
            }
        }
    }
}

<?php


namespace App\Service;

use App\Entity\Podcast;
use App\Entity\Source;
use App\Repository\PodcastRepository;

use App\Repository\SourceRepository;
use DateTime;
use Exception;
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

    private $requestUrl;
    private $apiCode;

    public function __construct(
        EntityManagerInterface $entityManager,
        PodcastRepository $podcastRepository,
        SourceRepository $sourceRepository,
        $requestUrl,
        $apiCode
    ) {
        $this->httpClient = HttpClient::create();
        $this->entityManager = $entityManager;
        $this->podcastRepository = $podcastRepository;
        $this->sourceRepository = $sourceRepository;
        $this->requestUrl = $requestUrl;
        $this->apiCode = $apiCode;
    }

    public function importDataFromYoutube(array &$listOfSources): bool
    {
        // TODO create IF sentence if last import ended

        /** @var Source $source */
        foreach ($listOfSources as $source) {
            $channelId = $this->getChannelId($source);
            try {
                $response = $this->httpClient->request('GET', $this->requestUrl . 'search', [
                    'query' => [
                        'part' => 'snippet',
                        'channelId' => $channelId,
                        'maxResults' => '5',
                        'order' => 'date',
                        'pageToken' => '',
                        'type' => 'video',
                        'key' => $this->apiCode
                    ]
                ]);
            } catch (TransportExceptionInterface $e) {
                dd($e);
                // TODO Write Exception
            }

            try {
                if ($response->getStatusCode() === 200) {
                    $content = $response->toArray();

                    foreach ($content['items'] as $video) {
                        if ($video['snippet']['liveBroadcastContent'] === 'none'
                            && !$this->isVideoExists($video['id']['videoId'])
                        ) {
                            $podcast = new Podcast();

                            $podcast->setSource($source);
                            $podcast->setVideo($video['id']['videoId']);
                            $podcast->setPublishedAt(new DateTime($video['snippet']['publishedAt']));
                            $podcast->setTitle($video['snippet']['title']);
                            $podcast->setImage(($video['snippet']['thumbnails']['high']['url']));
                            $podcast->setCreatedAt(new DateTime('now'));

                            $this->entityManager->persist($podcast);
                            $this->entityManager->flush();
                        }
                    }
                }
            } catch (ClientExceptionInterface $e) {
                dd($e);
                // TODO Write Exception
                return false;
            } catch (DecodingExceptionInterface $e) {
                dd($e);
                // TODO Write Exception
                return false;
            } catch (RedirectionExceptionInterface $e) {
                dd($e);
                // TODO Write Exception
                return false;
            } catch (ServerExceptionInterface $e) {
                dd($e);
                // TODO Write Exception
                return false;
            } catch (TransportExceptionInterface $e) {
                dd($e);
                // TODO Write Exception
                return false;
            } catch (Exception $e) {
                dd($e);
                // TODO Write Exception
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
            dd($e);
            // TODO Write Exception
        }

        try {
            if ($response->getStatusCode() === 200) {
                $content = $response->toArray();
                return $content['items'][0]['id'];
            }
        } catch (ClientExceptionInterface $e) {
            dd($e);
            // TODO Write Exception
            return false;
        } catch (DecodingExceptionInterface $e) {
            dd($e);
            // TODO Write Exception
            return false;
        } catch (RedirectionExceptionInterface $e) {
            dd($e);
            // TODO Write Exception
            return false;
        } catch (ServerExceptionInterface $e) {
            dd($e);
            // TODO Write Exception
            return false;
        } catch (TransportExceptionInterface $e) {
            dd($e);
            // TODO Write Exception
            return false;
        } catch (Exception $e) {
            dd($e);
            // TODO Write Exception
            return false;
        }
    }
}

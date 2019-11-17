<?php

namespace App\Service;

use App\Entity\Podcast;
use App\Entity\Source;
use App\Entity\Tag;
use App\Repository\PodcastRepository;
use App\Repository\TagRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DomCrawler\Crawler;

class CrawlerService
{
    private $entityManager;

    private $podcastRepository;

    private $tagRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        PodcastRepository $podcastRepository,
        TagRepository $tagRepository
    ) {
        $this->entityManager = $entityManager;
        $this->podcastRepository = $podcastRepository;
        $this->tagRepository = $tagRepository;
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
                        $podcast->setType(Podcast::TYPES['TYPE_AUDIO']);
                        $matchedTags = $this->findTagsInPodcast($podcast);

                        if (count($matchedTags) > 0) {
                            foreach ($matchedTags as $tag) {
                                $podcast->addTag($tag);
                            }
                        }

                        $this->entityManager->persist($podcast);

                        $podcasts[] = $podcast;
                    } else {
                        return;
                    }
                });
        }

        $this->entityManager->flush();

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

    /**
     * @param Podcast $podcast
     * @return Tag[]
     */
    private function findTagsInPodcast(Podcast $podcast)
    {
        $tags = $this->tagRepository->findAll();

        $podcastTags = [];

        foreach ($tags as $tag) {
            $tagName = $tag->getTag();

            if (strlen($tagName) > 3) {
                $tagName = (substr($tagName,0, strlen($tagName) -2));
            }

            if (strpos($podcast->getTitle(),$tagName) !== false
                || strpos($podcast->getDescription(), $tagName) ==! false) {

                $podcastTags[] = $tag;
            }
        }

        return $podcastTags;
    }
}

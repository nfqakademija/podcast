<?php

namespace App\Service;

use App\Repository\PodcastRepository;
use App\Repository\SourceRepository;
use App\Repository\TagRepository;
use Doctrine\ORM\EntityManagerInterface;

class SlugService
{
    /**
     * @var SourceRepository
     */
    private $sourceRepository;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var TagRepository
     */
    private $tagRepository;

    /**
     * @var PodcastRepository
     */
    private $podcastRepository;

    public function __construct(
        SourceRepository $sourceRepository,
        EntityManagerInterface $entityManager,
        TagRepository $tagRepository,
        PodcastRepository $podcastRepository
    ) {
        $this->sourceRepository = $sourceRepository;
        $this->entityManager = $entityManager;
        $this->tagRepository = $tagRepository;
        $this->podcastRepository = $podcastRepository;
    }

    public function makeSlugs()
    {
        $this->makeSlugsForSources();
        $this->makeSlugsForTags();
        $this->makeSlugsForPodcasts();

        $this->entityManager->flush();
    }

    private function generateSlug(string $sluggable)
    {
        $replacebleSymbols = ['.', ' ', ','];
        $trimableSymbols = [':', '"', "„", '“'];
        $result = str_replace($replacebleSymbols, '-', $sluggable);
        $result = strtolower(str_replace($trimableSymbols, '', $result));

        return $result;
    }

    private function makeSlugsForSources()
    {
        $sources = $this->sourceRepository->findAll();

        foreach ($sources as $source) {
            $source->setSlug($this->generateSlug($source->getName()));
        }
    }

    private function makeSlugsForTags()
    {
        $tags = $this->tagRepository->findAll();

        foreach ($tags as $tag) {
            $tag->setSlug($this->generateSlug($tag->getTag()));
        }
    }

    private function makeSlugsForPodcasts()
    {
        $podcasts = $this->podcastRepository->findAll();

        foreach ($podcasts as $podcast) {
            $podcast->setSlug($this->generateSlug($podcast->getTitle()));
        }
    }
}

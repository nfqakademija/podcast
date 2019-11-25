<?php


namespace App\Service;


use App\Entity\Podcast;
use App\Entity\Tag;
use App\Repository\TagRepository;

class TaggingService
{
    private $tagRepository;

    public function __construct(TagRepository $tagRepository)
    {
        $this->tagRepository = $tagRepository;
    }

    /**
     * @param Podcast $podcast
     * @return Tag[]
     */
    public function findTagsInPodcast(Podcast $podcast)
    {
        $tags = $this->tagRepository->findAll();

        $podcastTags = [];

        foreach ($tags as $tag) {
            $tagName = $tag->getTag();

            if (strlen($tagName) > 4) {
                $tagName = (substr($tagName, 0, strlen($tagName) - 2));
            }

            if (strpos($podcast->getTitle(), $tagName) !== false
                || strpos($podcast->getDescription(), $tagName) ==! false) {
                $podcastTags[] = $tag;
            }
        }

        return $podcastTags;
    }
}
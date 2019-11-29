<?php


namespace App\Service;

use App\Entity\Podcast;
use App\Entity\Tag;

class TaggingService
{
    /**
     * @param Podcast $podcast
     * @param array $tags
     * @return Tag[]
     */
    public function findTagsInPodcast(Podcast $podcast, array $tags)
    {
        $podcastTags = [];

        /** @var Tag $tag */
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

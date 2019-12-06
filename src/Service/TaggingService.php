<?php


namespace App\Service;

use App\Entity\Podcast;
use App\Entity\Tag;
use App\Repository\TagRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class TaggingService
{

    private $entityManager;

    private $tagRepository;

    private $security;

    public function __construct(
        EntityManagerInterface $entityManager,
        TagRepository $tagRepository,
        Security $security
    ) {
        $this->entityManager = $entityManager;
        $this->tagRepository = $tagRepository;
        $this->security = $security;
    }

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

    /**
     * @param array $submittedTags
     * @param array $existingUserTags
     */
    public function handleUserTags(array $submittedTags, array $existingUserTags): void
    {
        $user = $this->security->getUser();

        if ($submittedTags) {
            $this->addNewTagsToUser($submittedTags, $user);
            $this->removeTagsFromUser($submittedTags, $existingUserTags, $user);
        } else {
            foreach ($existingUserTags as $existingUserTag) {
                $user->removeTag($existingUserTag);
            }
        }

        $this->entityManager->flush();
    }

    /**
     * @param array $submittedTags
     * @param UserInterface|null $user
     */
    private function addNewTagsToUser(array $submittedTags, ?UserInterface $user): void
    {
        foreach ($submittedTags as $submittedTag) {
            $existingTag = $this->tagRepository->findOneBy(['tag' => $submittedTag]);
            if (!$existingTag) {
                $tag = new Tag();
                $tag->setTag($submittedTag);
                $this->entityManager->persist($tag);
                $user->addTag($tag);
            } else {
                $user->addTag($existingTag);
            }
        }
    }

    /**
     * @param array $submittedTags
     * @param array $userTags
     * @param UserInterface|null $user
     */
    private function removeTagsFromUser(array $submittedTags, array $userTags, ?UserInterface $user): void
    {
        foreach ($userTags as $userTag) {
            $tagExists = array_filter($submittedTags, function ($submittedTag) use ($userTag) {
                return $submittedTag === $userTag->getTag();
            });

            if (!$tagExists) {
                $user->removeTag($userTag);
            }
        }
    }
}

<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PodcastVotesRepository")
 */
class PodcastVote
{
    public const TYPES = [
        'TYPE_LIKE' => 'Liked',
        'TYPE_DISLIKE' => 'Disliked'
    ];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;


    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Podcast", inversedBy="podcasts")
     */
    private $podcast;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="users")
     */
    private $user;


    /**
     * @ORM\Column(type="string", length=255)
     */
    private $type;


    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getPodcast()
    {
        return $this->podcast;
    }

    /**
     * @param mixed $podcast
     * @return PodcastVote
     */
    public function setPodcast($podcast): self
    {
        $this->podcast = $podcast;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     * @return PodcastVote
     */
    public function setUser($user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     * @return PodcastVote
     */
    public function setType(String $type): self
    {
        $this->type = $type;

        return $this;
    }

}

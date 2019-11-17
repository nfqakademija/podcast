<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TagRepository")
 */
class Tag
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $tag;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Podcast", inversedBy="tags")
     */
    private $podcasts;

    public function __construct()
    {
        $this->podcasts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTag(): ?string
    {
        return $this->tag;
    }

    public function setTag(string $tag): self
    {
        $this->tag = $tag;

        return $this;
    }

    /**
     * @return Collection|Podcast[]
     */
    public function getPodcasts(): Collection
    {
        return $this->podcasts;
    }

    public function addPodcast(Podcast $podcast): self
    {
        if (!$this->podcasts->contains($podcast)) {
            $this->podcasts[] = $podcast;
        }

        return $this;
    }

    public function removePodcast(Podcast $podcast): self
    {
        if ($this->podcasts->contains($podcast)) {
            $this->podcasts->removeElement($podcast);
        }

        return $this;
    }

    public function __toString()
    {
        return $this->tag;
    }
}

<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TagRepository")
 * @ORM\Table(
 *     indexes={
 *          @ORM\Index(name="idx_tag", columns={"tag"})
 *     }
 * )
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

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\User", mappedBy="tags")
     */
    private $users;

    /**
     * @ORM\Column(type="string", length=255, nullable=true, unique=true)
     * @Gedmo\Slug(fields={"tag"})
     */
    private $slug;

    public function __construct()
    {
        $this->podcasts = new ArrayCollection();
        $this->users = new ArrayCollection();
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

    /**
     * @return Collection|User[]
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->addTag($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->contains($user)) {
            $this->users->removeElement($user);
            $user->removeTag($this);
        }

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }
}

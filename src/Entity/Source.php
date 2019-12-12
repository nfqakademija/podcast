<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SourceRepository")
 * @ORM\Table(
 *     indexes={
 *          @ORM\Index(name="idx_name", columns={"name"}),
 *          @ORM\Index(name="idx_slug", columns={"slug"})
 *     }
 * )
 */
class Source
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
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $url;

    /**
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="create")
     */
    private $createdAt;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Podcast", mappedBy="source")
     */
    private $podcasts;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $mainElementSelector;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $imageSelector;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $titleSelector;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $descriptionSelector;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $audioSelector;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $audioSourceAttribute;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $publicationDateSelector;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $imageSourceAttribute;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $sourceType;

    /**
     * @ORM\Column(type="string", length=255, nullable=true, unique=true)
     * @Gedmo\Slug(fields={"name"})
     */
    private $slug;

    public function __construct()
    {
        $this->podcasts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

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
            $podcast->setSource($this);
        }

        return $this;
    }

    public function removePodcast(Podcast $podcast): self
    {
        if ($this->podcasts->contains($podcast)) {
            $this->podcasts->removeElement($podcast);
            // set the owning side to null (unless already changed)
            if ($podcast->getSource() === $this) {
                $podcast->setSource(null);
            }
        }

        return $this;
    }

    public function __toString()
    {
        return $this->name;
    }

    public function getMainElementSelector(): ?string
    {
        return $this->mainElementSelector;
    }

    public function setMainElementSelector(?string $mainElementSelector): self
    {
        $this->mainElementSelector = $mainElementSelector;

        return $this;
    }

    public function getImageSelector(): ?string
    {
        return $this->imageSelector;
    }

    public function setImageSelector(?string $imageSelector): self
    {
        $this->imageSelector = $imageSelector;

        return $this;
    }

    public function getTitleSelector(): ?string
    {
        return $this->titleSelector;
    }

    public function setTitleSelector(?string $titleSelector): self
    {
        $this->titleSelector = $titleSelector;

        return $this;
    }

    public function getDescriptionSelector(): ?string
    {
        return $this->descriptionSelector;
    }

    public function setDescriptionSelector(?string $descriptionSelector): self
    {
        $this->descriptionSelector = $descriptionSelector;

        return $this;
    }

    public function getAudioSelector(): ?string
    {
        return $this->audioSelector;
    }

    public function setAudioSelector(?string $audioSelector): self
    {
        $this->audioSelector = $audioSelector;

        return $this;
    }

    public function getAudioSourceAttribute(): ?string
    {
        return $this->audioSourceAttribute;
    }

    public function setAudioSourceAttribute(?string $audioSourceAttribute): self
    {
        $this->audioSourceAttribute = $audioSourceAttribute;

        return $this;
    }

    public function getPublicationDateSelector(): ?string
    {
        return $this->publicationDateSelector;
    }

    public function setPublicationDateSelector(?string $publicationDateSelector): self
    {
        $this->publicationDateSelector = $publicationDateSelector;

        return $this;
    }

    public function getImageSourceAttribute(): ?string
    {
        return $this->imageSourceAttribute;
    }

    public function setImageSourceAttribute(?string $imageSourceAttribute): self
    {
        $this->imageSourceAttribute = $imageSourceAttribute;

        return $this;
    }

    public function getSourceType(): ?string
    {
        return $this->sourceType;
    }

    public function setSourceType(?string $sourceType): self
    {
        $this->sourceType = $sourceType;

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

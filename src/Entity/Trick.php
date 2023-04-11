<?php

namespace App\Entity;

use App\Repository\TrickRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;


#[UniqueEntity('name')]
#[ORM\HasLifecycleCallbacks()]
#[ORM\Entity(repositoryClass: TrickRepository::class)]

class Trick
{
    /**
     * @var int|null
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * @var string|null
     */
    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    private ?string $name = null;

    /**
     * @var string|null
     */
    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    private ?string $description = null;

    /**
     * @var string|null
     */
    #[ORM\Column(length: 50)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 50)]
    private ?string $tricksGroup = null;

    /**
     * @var array|null
     */
    #[ORM\Column(nullable: true)]
    private ?array $picture = null;

    /**
     * @var array|null
     */
    #[ORM\Column(nullable: true)]
    private ?array $video = null;

    /**
     * @var \DateTimeImmutable|null
     */
    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * @var \DateTimeImmutable|null
     */
    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * @var Collection|ArrayCollection
     */
    #[ORM\OneToMany(mappedBy: 'trick', targetEntity: Comment::class)]
    private Collection $comments;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable;
        $this->comments = new ArrayCollection();
    }

    /**
     * @return \DateTimeImmutable
     */
    #[ORM\PrePersist]
    public function setUpdatedAtValue(): \DateTimeImmutable
    {
        return $this->updatedAt = new \DateTimeImmutable;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getTricksGroup(): ?string
    {
        return $this->tricksGroup;
    }

    public function setTricksGroup(string $tricksGroup): self
    {
        $this->tricksGroup = $tricksGroup;

        return $this;
    }

    public function getPicture(): ?array
    {
        return $this->picture;
    }

    public function setPicture(?array $picture): self
    {
        $this->picture = $picture;

        return $this;
    }

    public function getVideo(): ?array
    {
        return $this->video;
    }

    public function setVideo(?array $video): self
    {
        $this->video = $video;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setTrick($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getTrick() === $this) {
                $comment->setTrick(null);
            }
        }

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}

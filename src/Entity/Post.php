<?php

namespace App\Entity;

use App\Repository\PostRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PostRepository::class)
 */
class Post
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="us_posts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $po_author_id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $po_created_at;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $po_edited_at;

    /**
     * @ORM\Column(type="json")
     */
    private $po_content = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPoAuthorId(): ?User
    {
        return $this->po_author_id;
    }

    public function setPoAuthorId(?User $po_author_id): self
    {
        $this->po_author_id = $po_author_id;

        return $this;
    }

    public function getPoCreatedAt(): ?\DateTimeInterface
    {
        return $this->po_created_at;
    }

    public function setPoCreatedAt(\DateTimeInterface $po_created_at): self
    {
        $this->po_created_at = $po_created_at;

        return $this;
    }

    public function getPoEditedAt(): ?\DateTimeInterface
    {
        return $this->po_edited_at;
    }

    public function setPoEditedAt(?\DateTimeInterface $po_edited_at): self
    {
        $this->po_edited_at = $po_edited_at;

        return $this;
    }

    public function getPoContent(): ?array
    {
        return $this->po_content;
    }

    public function setPoContent(array $po_content): self
    {
        $this->po_content = $po_content;

        return $this;
    }
}

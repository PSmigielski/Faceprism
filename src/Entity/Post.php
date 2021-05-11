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
     * @ORM\JoinColumn(name="po_author_id",referencedColumnName = "us_id", nullable=false)
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
     * @ORM\Column(type="text",length=1024, nullable=true)
     */
    private $po_text;

    /**
     * @ORM\Column(type="blob", nullable=true)
     */
    private $po_image;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAuthorId(): ?User
    {
        return $this->po_author_id;
    }

    public function setAuthorId(?User $po_author_id): self
    {
        $this->po_author_id = $po_author_id;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->po_created_at;
    }

    public function setCreatedAt(\DateTimeInterface $po_created_at): self
    {
        $this->po_created_at = $po_created_at;

        return $this;
    }

    public function getEditedAt(): ?\DateTimeInterface
    {
        return $this->po_edited_at;
    }

    public function setEditedAt(?\DateTimeInterface $po_edited_at): self
    {
        $this->po_edited_at = $po_edited_at;

        return $this;
    }

    public function getText(): ?string
    {
        return $this->po_text;
    }

    public function setText(string $po_text): self
    {
        $this->po_text = $po_text;

        return $this;
    }

    public function getImage()
    {
        return $this->po_image;
    }

    public function setImage($po_image): self
    {
        $this->po_image = $po_image;

        return $this;
    }
}

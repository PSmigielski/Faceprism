<?php

namespace App\Entity;

use App\Repository\CommentRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CommentRepository::class)
 */
class Comment
{
    /**
     * @ORM\Id
     * @ORM\Column(name="co_id", type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private $co_id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(name="co_author",referencedColumnName = "us_id", nullable=false,onDelete="CASCADE")
     */
    private $co_author;

    /**
     * @ORM\ManyToOne(targetEntity=Post::class, inversedBy="po_comments")
     * @ORM\JoinColumn(name="co_post",referencedColumnName = "po_id", nullable=false)
     */
    private $co_post;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $co_text;

    /**
     * @ORM\Column(type="datetime")
     */
    private $co_created_at;

    /**
     * @ORM\Column(type="datetime",nullable=true)
     */
    private $co_edited_at;

    /**
     * @ORM\ManyToOne(targetEntity=Comment::class)
     * @ORM\JoinColumn(name="co_reply_to",referencedColumnName = "co_id", nullable=true,onDelete="CASCADE")
     */
    private $co_reply_to;

    /**
     * @ORM\Column(type="integer")
     */
    private $co_replies_count;

    public function getId(): ?string
    {
        return $this->co_id;
    }

    public function getAuthor(): ?User
    {
        return $this->co_author;
    }

    public function setAuthor(?User $co_author): self
    {
        $this->co_author = $co_author;

        return $this;
    }

    public function getPost(): ?Post
    {
        return $this->co_post;
    }

    public function setPost(?Post $co_post): self
    {
        $this->co_post = $co_post;

        return $this;
    }

    public function getText(): ?string
    {
        return $this->co_text;
    }

    public function setText(string $co_text): self
    {
        $this->co_text = $co_text;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->co_created_at;
    }

    public function setCreatedAt(\DateTimeInterface $co_created_at): self
    {
        $this->co_created_at = $co_created_at;

        return $this;
    }

    public function getEditedAt(): ?\DateTimeInterface
    {
        return $this->co_edited_at;
    }

    public function setEditedAt(\DateTimeInterface $co_edited_at): self
    {
        $this->co_edited_at = $co_edited_at;

        return $this;
    }

    public function getReplyTo(): ?self
    {
        return $this->co_reply_to;
    }

    public function setReplyTo(?self $co_reply_to): self
    {
        $this->co_reply_to = $co_reply_to;
        return $this;
    }

    public function getRepliesCount(): ?int
    {
        return $this->co_replies_count;
    }

    public function setRepliesCount(int $co_replies_count): self
    {
        $this->co_replies_count = $co_replies_count;

        return $this;
    }
}

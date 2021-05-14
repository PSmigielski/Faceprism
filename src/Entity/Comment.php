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
     * @ORM\JoinColumn(name="co_author",referencedColumnName = "us_id", nullable=false)
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
     * @ORM\OneToOne(targetEntity=Comment::class, inversedBy="co_reply", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="co_reply_to",referencedColumnName = "co_id", nullable=true)
     */
    private $co_reply_to;

    /**
     * @ORM\OneToOne(targetEntity=Comment::class, mappedBy="co_reply_to", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="co_reply",referencedColumnName = "co_id", nullable=true)
     */
    private $co_reply;

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

    public function getReplies(): ?self
    {
        return $this->co_reply;
    }

    public function setReplies(?self $co_reply): self
    {
        // unset the owning side of the relation if necessary
        if ($co_reply === null && $this->co_reply !== null) {
            $this->co_reply->setCoReplyTo(null);
        }

        // set the owning side of the relation if necessary
        if ($co_reply !== null && $co_reply->getReplyTo() !== $this) {
            $co_reply->setReplyTo($this);
        }

        $this->co_replies = $co_reply;

        return $this;
    }
}

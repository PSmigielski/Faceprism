<?php

namespace App\Entity;

use App\Repository\PostRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PostRepository::class)
 */
class Post
{
    /**
     * @ORM\Id
     * @ORM\Column(name="po_id", type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private $po_id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="us_posts")
     * @ORM\JoinColumn(name="po_author",referencedColumnName = "us_id", nullable=false, onDelete="CASCADE")
     */
    private $po_author;

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
     * @ORM\Column(type="string", nullable=true)
     */
    private $po_file_url;

    /**
     * @ORM\OneToMany(targetEntity=Comment::class, mappedBy="co_post", orphanRemoval=true)
     */
    private $po_comments;

    /**
     * @ORM\Column(type="integer")
     */
    private $po_like_count;

    /**
     * @ORM\Column(type="integer")
     */
    private $po_comment_count;

    /**
     * @ORM\ManyToOne(targetEntity=Page::class)
     * @ORM\JoinColumn(name="po_page",referencedColumnName = "pa_id", nullable=true, onDelete="CASCADE")
     */
    private $po_page;

    public function __construct()
    {
        $this->po_comments = new ArrayCollection();
    }

    public function getId(): ?string
    {
        return $this->po_id;
    }

    public function getAuthor(): ?User
    {
        return $this->po_author;
    }

    public function setAuthor(?User $po_author): self
    {
        $this->po_author = $po_author;

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

    public function getFileUrl()
    {
        return $this->po_file_url;
    }

    public function setFileUrl($po_file_url): self
    {
        $this->po_file_url = $po_file_url;
        return $this;
    }

    /**
     * @return Collection|Comment[]
     */
    public function getPoComments(): Collection
    {
        return $this->po_comments;
    }

    public function addPoComment(Comment $poComment): self
    {
        if (!$this->po_comments->contains($poComment)) {
            $this->po_comments[] = $poComment;
            $poComment->setPost($this);
        }

        return $this;
    }

    public function removePoComment(Comment $poComment): self
    {
        if ($this->po_comments->removeElement($poComment)) {
            // set the owning side to null (unless already changed)
            if ($poComment->getPost() === $this) {
                $poComment->setPost(null);
            }
        }

        return $this;
    }

    public function getLikeCount(): ?int
    {
        return $this->po_like_count;
    }

    public function setLikeCount(int $po_like_count): self
    {
        $this->po_like_count = $po_like_count;

        return $this;
    }

    public function getCommentCount(): ?int
    {
        return $this->po_comment_count;
    }

    public function setCommentCount(int $po_comment_count): self
    {
        $this->po_comment_count = $po_comment_count;

        return $this;
    }

    public function getPage(): ?Page
    {
        return $this->po_page;
    }

    public function setPage(?Page $po_page): self
    {
        $this->po_page = $po_page;

        return $this;
    }
}

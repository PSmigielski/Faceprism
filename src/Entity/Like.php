<?php

namespace App\Entity;

use App\Repository\LikeRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=LikeRepository::class)
 * @ORM\Table(name="`like`")
 */
class Like
{
    /**
     * @ORM\Id
     * @ORM\Column(name="li_id", type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private $li_id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(name="li_user",referencedColumnName = "us_id", nullable=false)
     */
    private $li_user;

    /**
     * @ORM\ManyToOne(targetEntity=Post::class)
     * @ORM\JoinColumn(name="li_post",referencedColumnName = "po_id", nullable=false)
     */
    private $li_post;

    public function getId(): ?string
    {
        return $this->li_id;
    }

    public function getUser(): ?User
    {
        return $this->li_user;
    }

    public function setUser(?User $li_user): self
    {
        $this->li_user = $li_user;

        return $this;
    }

    public function getPost(): ?Post
    {
        return $this->li_post;
    }

    public function setPost(?Post $li_post): self
    {
        $this->li_post = $li_post;

        return $this;
    }
}

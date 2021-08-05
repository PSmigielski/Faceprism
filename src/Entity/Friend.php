<?php

namespace App\Entity;

use App\Repository\FriendRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=FriendRepository::class)
 */
class Friend
{
    /**
     * @ORM\Id
     * @ORM\Column(name="fr_id", type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private $fr_id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(name="fr_user",referencedColumnName = "us_id", nullable=false,onDelete="CASCADE")
     */
    private $fr_user;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(name="fr_friend",referencedColumnName = "us_id",nullable=false,onDelete="CASCADE")
     */
    private $fr_friend;

    /**
     * @ORM\Column(type="datetime")
     */
    private $fr_accept_date;

    /**
     * @ORM\Column(type="boolean")
     */
    private $fr_is_blocked;

    public function getId(): ?string
    {
        return $this->fr_id;
    }

    public function getUser(): ?User
    {
        return $this->fr_user;
    }

    public function setUser(?User $fr_user): self
    {
        $this->fr_user = $fr_user;

        return $this;
    }

    public function getFriend(): ?User
    {
        return $this->fr_friend;
    }

    public function setFriend(?User $fr_friend): self
    {
        $this->fr_friend = $fr_friend;

        return $this;
    }

    public function getAcceptDate(): ?\DateTimeInterface
    {
        return $this->fr_accept_date;
    }

    public function setAcceptDate(\DateTimeInterface $fr_accept_date): self
    {
        $this->fr_accept_date = $fr_accept_date;

        return $this;
    }

    public function getIsBlocked()
    {
        return $this->fr_is_blocked;
    }

    public function setIsBlocked($fr_is_blocked): self
    {
        $this->fr_is_blocked = $fr_is_blocked;

        return $this;
    }

}

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
     * @ORM\JoinColumn(name="fr_user",referencedColumnName = "us_id", nullable=false)
     */
    private $fr_user;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(name="fr_friend",referencedColumnName = "us_id",nullable=false)
     */
    private $fr_friend;

    public function getId(): ?string
    {
        return $this->fr_id;
    }

    public function getFrUser(): ?User
    {
        return $this->fr_user;
    }

    public function setFrUser(?User $fr_user): self
    {
        $this->fr_user = $fr_user;

        return $this;
    }

    public function getFrFriend(): ?User
    {
        return $this->fr_friend;
    }

    public function setFrFriend(?User $fr_friend): self
    {
        $this->fr_friend = $fr_friend;

        return $this;
    }

}

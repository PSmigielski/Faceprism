<?php

namespace App\Entity;

use App\Repository\FriendRequestRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=FriendRequestRepository::class)
 */
class FriendRequest
{
    /**
     * @ORM\Id
     * @ORM\Column(name="fr_req_id", type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private $fr_req_id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(name="fr_req_user",referencedColumnName = "us_id",nullable=false)
     */
    private $fr_req_user;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(name="fr_req_friend",referencedColumnName = "us_id",nullable=false)
     */
    private $fr_req_friend;

    /**
     * @ORM\Column(type="datetime")
     */
    private $fr_req_requestDate;

    /**
     * @ORM\Column(type="boolean", options={"default" : 0})
     */
    private $fr_req_accepted;

    public function getId(): ?string
    {
        return $this->fr_req_id;
    }

    public function getUser(): ?User
    {
        return $this->fr_req_user;
    }

    public function setUser(?User $fr_req_user): self
    {
        $this->fr_req_user = $fr_req_user;

        return $this;
    }

    public function getFriend(): ?User
    {
        return $this->fr_req_friend;
    }

    public function setFriend(?User $fr_req_friend): self
    {
        $this->fr_req_friend = $fr_req_friend;

        return $this;
    }

    public function getRequestDate(): ?\DateTimeInterface
    {
        return $this->fr_req_requestDate;
    }

    public function setRequestDate(\DateTimeInterface $fr_req_requestDate): self
    {
        $this->fr_req_requestDate = $fr_req_requestDate;

        return $this;
    }

    public function getAccepted(): ?bool
    {
        return $this->fr_req_accepted;
    }

    public function setAccepted(bool $fr_req_accepted): self
    {
        $this->fr_req_accepted = $fr_req_accepted;

        return $this;
    }
}

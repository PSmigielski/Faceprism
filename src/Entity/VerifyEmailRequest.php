<?php

namespace App\Entity;

use App\Repository\VerifyEmailRequestRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=VerifyEmailRequestRepository::class)
 */
class VerifyEmailRequest
{
    /**
     * @ORM\Id
     * @ORM\Column(name="ver_id", type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private $ver_id;

    /**
     * @ORM\OneToOne(targetEntity=User::class, cascade={"persist", "remove"})
     * @ORM\JoinColumn(referencedColumnName = "us_id",nullable=false)
     */
    private $ver_user;

    /**
     * @ORM\Column(type="datetime", length=10)
     */
    private $ver_requested_at;

    /**
     * @ORM\Column(type="datetime")
     */
    private $ver_expires_at;

    public function getId(): ?string
    {
        return $this->ver_id;
    }

    public function getUser(): ?User
    {
        return $this->ver_user;
    }

    public function setUser(User $user): self
    {
        $this->ver_user = $user;

        return $this;
    }


    public function getRequestedAt(): ?\DateTimeInterface
    {
        return $this->ver_requested_at;
    }

    public function setRequestedAt(\DateTimeInterface $ver_requested_at): self
    {
        $this->ver_requested_at = $ver_requested_at;

        return $this;
    }

    public function getExpiresAt(): ?\DateTimeInterface
    {
        return $this->ver_expires_at;
    }

    public function setExpiresAt(\DateTimeInterface $ver_expires_at): self
    {
        $this->ver_expires_at = $ver_expires_at;

        return $this;
    }
}

<?php

namespace App\Entity;

use App\Repository\FollowRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=FollowRepository::class)
 */
class Follow
{
    /**
     * @ORM\Id
     * @ORM\Column(name="fo_id", type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private $fo_id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(referencedColumnName = "us_id", nullable=false)
     */
    private $fo_user;

    /**
     * @ORM\ManyToOne(targetEntity=Page::class)
     * @ORM\JoinColumn(referencedColumnName = "pa_id", nullable=false)
     */
    private $fo_page;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->fo_user;
    }

    public function setUser(?User $fo_user): self
    {
        $this->fo_user = $fo_user;

        return $this;
    }

    public function getPage(): ?Page
    {
        return $this->fo_page;
    }

    public function setPage(?Page $fo_page): self
    {
        $this->fo_page = $fo_page;

        return $this;
    }
}

<?php

namespace App\Entity;

use App\Repository\PageModerationRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PageModerationRepository::class)
 */
class PageModeration
{
    /**
     * @ORM\Id
     * @ORM\Column(name="pa_id", type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private $pm_id;

    /**
     * @ORM\ManyToOne(targetEntity=Page::class)
     * @ORM\JoinColumn(name="pm_page_id",referencedColumnName = "pa_id",nullable=false, onDelete="CASCADE")
     */
    private $pm_page_id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(name="pa_owner",referencedColumnName = "us_id",nullable=false, onDelete="CASCADE")
     */
    private $pm_user_id;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $pm_page_role;

    public function getId(): ?string
    {
        return $this->pm_id;
    }

    public function getPmPageId(): ?Page
    {
        return $this->pm_page_id;
    }

    public function setPmPageId(?Page $pm_page_id): self
    {
        $this->pm_page_id = $pm_page_id;

        return $this;
    }

    public function getPmUserId(): ?User
    {
        return $this->pm_user_id;
    }

    public function setPmUserId(?User $pm_user_id): self
    {
        $this->pm_user_id = $pm_user_id;

        return $this;
    }

    public function getPmPageRole(): ?string
    {
        return $this->pm_page_role;
    }

    public function setPmPageRole(string $pm_page_role): self
    {
        $this->pm_page_role = $pm_page_role;

        return $this;
    }
}

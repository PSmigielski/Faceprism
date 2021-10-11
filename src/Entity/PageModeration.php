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
     * @ORM\Column(name="pm_id", type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private $pm_id;

    /**
     * @ORM\ManyToOne(targetEntity=Page::class)
     * @ORM\JoinColumn(name="pm_page",referencedColumnName = "pa_id",nullable=false, onDelete="CASCADE")
     */
    private $pm_page;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(name="pm_user",referencedColumnName = "us_id",nullable=false, onDelete="CASCADE")
     */
    private $pm_user;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $pm_page_role;

    public function getId(): ?string
    {
        return $this->pm_id;
    }

    public function getPage(): ?Page
    {
        return $this->pm_page;
    }

    public function setPage(?Page $pm_page): self
    {
        $this->pm_page = $pm_page;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->pm_user;
    }

    public function setUser(?User $pm_user): self
    {
        $this->pm_user = $pm_user;

        return $this;
    }

    public function getPageRole(): ?string
    {
        return $this->pm_page_role;
    }

    public function setPageRole(string $pm_page_role): self
    {
        $this->pm_page_role = $pm_page_role;

        return $this;
    }
}

<?php

namespace App\Entity;

use App\Repository\PageRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PageRepository::class)
 */
class Page
{
    /**
     * @ORM\Id
     * @ORM\Column(name="pa_id", type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private $pa_id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $pa_name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $pa_bio;

    /**
     * @ORM\Column(type="integer")
     */
    private $pa_follow_count;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $pa_profile_pic_url;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $pa_banner_url;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $pa_email;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $pa_website;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(name="pa_owner",referencedColumnName = "us_id",nullable=false,onDelete="CASCADE")
     */
    private $pa_owner;

    public function getId(): ?string
    {
        return $this->pa_id;
    }

    public function getName(): ?string
    {
        return $this->pa_name;
    }

    public function setName(string $pa_name): self
    {
        $this->pa_name = $pa_name;

        return $this;
    }

    public function getBio(): ?string
    {
        return $this->pa_bio;
    }

    public function setBio(string $pa_bio): self
    {
        $this->pa_bio = $pa_bio;

        return $this;
    }

    public function getFollowCount(): ?int
    {
        return $this->pa_follow_count;
    }

    public function setFollowCount(int $pa_follow_count): self
    {
        $this->pa_follow_count = $pa_follow_count;

        return $this;
    }

    public function getProfilePicUrl(): ?string
    {
        return $this->pa_profile_pic_url;
    }

    public function setProfilePicUrl(string $pa_profile_pic_url): self
    {
        $this->pa_profile_pic_url = $pa_profile_pic_url;

        return $this;
    }

    public function getBannerUrl(): ?string
    {
        return $this->pa_banner_url;
    }

    public function setBannerUrl(?string $pa_banner_url): self
    {
        $this->pa_banner_url = $pa_banner_url;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->pa_email;
    }

    public function setEmail(?string $pa_email): self
    {
        $this->pa_email = $pa_email;

        return $this;
    }

    public function getWebsite(): ?string
    {
        return $this->pa_website;
    }

    public function setWebsite(?string $pa_website): self
    {
        $this->pa_website = $pa_website;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->pa_owner;
    }

    public function setOwner(?User $pa_owner): self
    {
        $this->pa_owner = $pa_owner;

        return $this;
    }
}

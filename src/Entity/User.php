<?php

namespace App\Entity;

use App\Repository\UserRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 */
class User implements UserInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(name="us_id", type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private $us_id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $us_email;

    /**
     * @ORM\Column(type="json")
     */
    private $us_roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $us_password;

    /**
     * @ORM\Column(type="string", length=30)
     */
    private $us_name;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $us_surname;

    /**
     * @ORM\Column(type="date")
     */
    private $us_date_of_birth;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $us_gender;

    /**
     * @ORM\OneToMany(targetEntity=Post::class, mappedBy="po_author", orphanRemoval=true)
     */
    private $us_posts;

    /**
     * @ORM\Column(type="boolean")
     */
    private $us_verified;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $us_profile_pic_url;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $us_banner_url;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $us_bio;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $us_tag;

    public function __construct(array $data = null)
    {
        if (!is_null($data)) {
            $this->setEmail($data["email"]);
            $this->setDateOfBirth(new DateTime($data["date_of_birth"]));
            $this->setGender($data["gender"]);
            $this->setName($data["name"]);
            $this->setSurname($data["surname"]);
            $this->setRoles([]);
            $this->setVerified(false);
            $this->setProfilePicUrl("https://res.cloudinary.com/faceprism/image/upload/v1626432519/profile_pics/default_bbdyw0.png");
            $this->setTag($data["tag"]);
            if (isset($data["bio"])) {
                $this->setBio($data["bio"]);
            }
        }
        $this->us_posts = new ArrayCollection();
    }

    public function getId(): ?string
    {
        return $this->us_id;
    }

    public function getEmail(): ?string
    {
        return $this->us_email;
    }

    public function setEmail(string $email): self
    {
        $this->us_email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->us_email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->us_roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->us_roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->us_password;
    }

    public function setPassword(string $password): self
    {
        $this->us_password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getName(): ?string
    {
        return $this->us_name;
    }

    public function setName(string $us_name): self
    {
        $this->us_name = $us_name;

        return $this;
    }

    public function getSurname(): ?string
    {
        return $this->us_surname;
    }

    public function setSurname(string $us_surname): self
    {
        $this->us_surname = $us_surname;

        return $this;
    }

    public function getDateOfBirth(): ?\DateTimeInterface
    {
        return $this->us_date_of_birth;
    }

    public function setDateOfBirth(\DateTimeInterface $us_date_of_birth): self
    {
        $this->us_date_of_birth = $us_date_of_birth;

        return $this;
    }

    public function getGender(): ?string
    {
        return $this->us_gender;
    }

    public function setGender(string $us_gender): self
    {
        $this->us_gender = $us_gender;

        return $this;
    }

    /**
     * @return Collection|Post[]
     */
    public function getPosts(): Collection
    {
        return $this->us_posts;
    }

    public function addPost(Post $usPost): self
    {
        if (!$this->us_posts->contains($usPost)) {
            $this->us_posts[] = $usPost;
            $usPost->setAuthor($this);
        }

        return $this;
    }

    public function removePost(Post $usPost): self
    {
        if ($this->us_posts->removeElement($usPost)) {
            // set the owning side to null (unless already changed)
            if ($usPost->getAuthor() === $this) {
                $usPost->setAuthor(null);
            }
        }

        return $this;
    }

    public function getVerified(): ?bool
    {
        return $this->us_verified;
    }

    public function setVerified(bool $us_verified): self
    {
        $this->us_verified = $us_verified;
        return $this;
    }

    public function getProfilePicUrl(): ?string
    {
        return $this->us_profile_pic_url;
    }

    public function setProfilePicUrl(string $us_profile_pic_url): self
    {
        $this->us_profile_pic_url = $us_profile_pic_url;
        return $this;
    }

    public function getBannerUrl(): ?string
    {
        return $this->us_banner_url;
    }

    public function setBannerUrl(?string $us_banner_url): self
    {
        $this->us_banner_url = $us_banner_url;
        return $this;
    }

    public function getBio(): ?string
    {
        return $this->us_bio;
    }

    public function setBio(?string $us_bio): self
    {
        $this->us_bio = $us_bio;
        return $this;
    }

    public function getTag(): ?string
    {
        return $this->us_tag;
    }

    public function setTag(string $us_tag): self
    {
        $this->us_tag = $us_tag;
        return $this;
    }
}

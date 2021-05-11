<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
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
     * @ORM\OneToMany(targetEntity=Post::class, mappedBy="po_author_id", orphanRemoval=true)
     */
    private $us_posts;

    public function __construct()
    {
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
    public function getUsPosts(): Collection
    {
        return $this->us_posts;
    }

    public function addUsPost(Post $usPost): self
    {
        if (!$this->us_posts->contains($usPost)) {
            $this->us_posts[] = $usPost;
            $usPost->setPoAuthorId($this);
        }

        return $this;
    }

    public function removeUsPost(Post $usPost): self
    {
        if ($this->us_posts->removeElement($usPost)) {
            // set the owning side to null (unless already changed)
            if ($usPost->getPoAuthorId() === $this) {
                $usPost->setPoAuthorId(null);
            }
        }

        return $this;
    }
}

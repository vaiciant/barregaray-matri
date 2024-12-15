<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    /**
     * @var Collection<int, Persona>
     */
    #[ORM\OneToMany(targetEntity: Persona::class, mappedBy: 'user')]
    private Collection $personas;

    #[ORM\Column]
    private ?bool $temp_pass = null;

    /**
     * @var Collection<int, Cambios>
     */
    #[ORM\OneToMany(targetEntity: Cambios::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $cambios;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $created_time = null;

    public function __construct()
    {
        $this->created_time = new \DateTime();
        $this->personas = new ArrayCollection();
        $this->cambios = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * @return Collection<int, Persona>
     */
    public function getPersonas(): Collection
    {
        return $this->personas;
    }

    public function addPersona(Persona $persona): static
    {
        if (!$this->personas->contains($persona)) {
            $this->personas->add($persona);
            $persona->setUser($this);
        }

        return $this;
    }

    public function removePersona(Persona $persona): static
    {
        if ($this->personas->removeElement($persona)) {
            // set the owning side to null (unless already changed)
            if ($persona->getUser() === $this) {
                $persona->setUser(null);
            }
        }

        return $this;
    }

    public function isTempPass(): ?bool
    {
        return $this->temp_pass;
    }

    public function setTempPass(bool $temp_pass): static
    {
        $this->temp_pass = $temp_pass;

        return $this;
    }

    /**
     * @return Collection<int, Cambios>
     */
    public function getCambios(): Collection
    {
        return $this->cambios;
    }

    public function addCambio(Cambios $cambio): static
    {
        if (!$this->cambios->contains($cambio)) {
            $this->cambios->add($cambio);
            $cambio->setUser($this);
        }

        return $this;
    }

    public function removeCambio(Cambios $cambio): static
    {
        if ($this->cambios->removeElement($cambio)) {
            // set the owning side to null (unless already changed)
            if ($cambio->getUser() === $this) {
                $cambio->setUser(null);
            }
        }

        return $this;
    }

    public function getCreatedTime(): ?\DateTimeInterface
    {
        return $this->created_time;
    }

    public function setCreatedTime(\DateTimeInterface $created_time): static
    {
        $this->created_time = $created_time;

        return $this;
    }
}

<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
    /**
     * @Assert\Email(message = "Email nije u dobrom formatu")
     * @Assert\NotBlank(message = "Email je obavezan.")
     */
    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     * @Assert\Regex(
     *     pattern="/(.*[A-Z].*)$/",
     *     match=true,
     *     message="Lozinka mora da sadrzi jedno veliko slovo."
     * )
     * @Assert\Regex(
     *     pattern="/(.*[a-z].*)$/",
     *     match=true,
     *     message="Lozinka mora da sadrzi jedno malo slovo."
     * )
     * @Assert\Regex(
     *     pattern="/(.*[0-9].*)$/",
     *     match=true,
     *     message="Lozinka mora da sadrzi jednu cifru."
     * )
     * @Assert\Regex(
     *     pattern="/(.*[.,!@#$%^&*()].*)$/",
     *     match=true,
     *     message="Lozinka mora da sadrzi jedan specijalni karakter."
     * )
     * @Assert\Length(
     *      min = 15,
     *      minMessage = "Lozinka mora imati {{ limit }} karaktera",
     * )
     */
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $password = null;

    /**
     * @Assert\Regex(pattern="/^[A-z]+$/", message="Ime moze da sadrzi samo slova.")
     */
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name = null;

    /**
     * @Assert\Regex(pattern="/^[A-z]+$/", message="Prezime moze da sadrzi samo slova.")
     */
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $surname = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $confirmationCode = null;

    #[ORM\Column(length: 255, nullable: false)]
    private ?bool $confirmed = false;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: PasswordResets::class)]
    private Collection $passwordResets;

    public function __construct()
    {
        $this->passwordResets = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
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
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
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
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getSurname(): ?string
    {
        return $this->surname;
    }

    public function setSurname(?string $surname): self
    {
        $this->surname = $surname;

        return $this;
    }

    public function getConfirmationCode(): ?string
    {
        return $this->confirmationCode;
    }

    public function setConfirmationCode(?string $confirmationCode): self
    {
        $this->confirmationCode = $confirmationCode;

        return $this;
    }

    public function getConfirmed(): ?string
    {
        return $this->confirmed;
    }

    public function setConfirmed(?bool $confirmed): self
    {
        $this->confirmed = $confirmed;

        return $this;
    }

    /**
     * @return Collection<int, PasswordResets>
     */
    public function getPasswordResets(): Collection
    {
        return $this->passwordResets;
    }

    public function addPasswordReset(PasswordResets $passwordReset): self
    {
        if (!$this->passwordResets->contains($passwordReset)) {
            $this->passwordResets->add($passwordReset);
            $passwordReset->setUser($this);
        }

        return $this;
    }

    public function removePasswordReset(PasswordResets $passwordReset): self
    {
        if ($this->passwordResets->removeElement($passwordReset)) {
            // set the owning side to null (unless already changed)
            if ($passwordReset->getUser() === $this) {
                $passwordReset->setUser(null);
            }
        }

        return $this;
    }
}

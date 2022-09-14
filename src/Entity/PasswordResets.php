<?php

namespace App\Entity;

use App\Repository\PasswordResetsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PasswordResetsRepository::class)]
class PasswordResets
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $token = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $RequestedAt = null;

    #[ORM\ManyToOne(inversedBy: 'passwordResets')]
    private ?User $user = null;

    #[ORM\Column]
    private ?bool $didReset = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function getRequestedAt(): ?\DateTimeInterface
    {
        return $this->RequestedAt;
    }

    public function setRequestedAt(\DateTimeInterface $RequestedAt): self
    {
        $this->RequestedAt = $RequestedAt;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function isDidReset(): ?bool
    {
        return $this->didReset;
    }

    public function setDidReset(bool $didReset): self
    {
        $this->didReset = $didReset;

        return $this;
    }
}

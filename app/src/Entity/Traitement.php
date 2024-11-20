<?php

namespace App\Entity;

use App\Repository\TraitementRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TraitementRepository::class)]
class Traitement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateRenouvellement = null;

    #[ORM\Column(nullable: true)]
    private ?int $dose = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $frequence = null;

    #[ORM\Column]
    private ?bool $actif = null;

    #[ORM\ManyToOne(targetEntity: "App\Entity\User", inversedBy: "traitements")]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: "App\Entity\Medicament", inversedBy: "traitements")]
    #[ORM\JoinColumn(nullable: false)]
    private ?Medicament $medicament = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateRenouvellement(): ?\DateTimeInterface
    {
        return $this->dateRenouvellement;
    }

    public function setDateRenouvellement(?\DateTimeInterface $dateRenouvellement): static
    {
        $this->dateRenouvellement = $dateRenouvellement;

        return $this;
    }

    public function getDose(): ?int
    {
        return $this->dose;
    }

    public function setDose(?int $dose): static
    {
        $this->dose = $dose;

        return $this;
    }

    public function getFrequence(): ?string
    {
        return $this->frequence;
    }

    public function setFrequence(?string $frequence): static
    {
        $this->frequence = $frequence;

        return $this;
    }

    public function isActif(): ?bool
    {
        return $this->actif;
    }

    public function setActif(bool $actif): static
    {
        $this->actif = $actif;

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

    public function getMedicament(): ?Medicament
    {
        return $this->medicament;
    }

    public function setMedicament(?Medicament $medicament): self
    {
        $this->medicament = $medicament;
        return $this;
    }
}

<?php

namespace App\Entity;

use App\Repository\InventaireRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InventaireRepository::class)]
class Inventaire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $quantite = null;

    #[ORM\ManyToOne(targetEntity: "App\Entity\User", inversedBy: "inventaires")]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: "App\Entity\Medicament", inversedBy: "inventaires")]
    #[ORM\JoinColumn(nullable: false)]
    private ?Medicament $medicament = null;

    #[ORM\Column]
    private ?int $nbBoite = null;

    public function getNbBoite(): ?int
    {
        return $this->nbBoite;
    }

    public function setNbBoite(int $nbBoite): static
    {
        $this->nbBoite = $nbBoite;

        return $this;
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuantite(): ?int
    {
        return $this->quantite;
    }

    public function setQuantite(int $quantite): static
    {
        $this->quantite = $quantite;

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

<?php

namespace App\Entity;

use App\Repository\MedicamentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MedicamentRepository::class)]
class Medicament
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $codeCIS = null;

    #[ORM\Column(length: 500)]
    private ?string $nom = null;

    #[ORM\Column(length: 500)]
    private ?string $formePharmaceutique = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $voieAdministration = null;

    #[ORM\Column(length: 500)]
    private ?string $etatAutorisation = null;

    #[ORM\Column(name: "`procedure`", type: "string", length: 500)]
    private ?string $procedure = null;

    #[ORM\Column(length: 500)]
    private ?string $etatCommercialisation = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dateCommercialisation = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $fabricant = null;

    #[ORM\OneToMany(mappedBy: "medicament", targetEntity: "App\Entity\Traitement")]
    private $traitements;

    #[ORM\OneToMany(mappedBy: "medicament", targetEntity: "App\Entity\Inventaire")]
    private $inventaires;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCodeCIS(): ?string
    {
        return $this->codeCIS;
    }

    public function setCodeCIS(string $codeCIS): static
    {
        $this->codeCIS = $codeCIS;

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getFormePharmaceutique(): ?string
    {
        return $this->formePharmaceutique;
    }

    public function setFormePharmaceutique(string $formePharmaceutique): static
    {
        $this->formePharmaceutique = $formePharmaceutique;

        return $this;
    }

    public function getVoieAdministration(): ?string
    {
        return $this->voieAdministration;
    }

    public function setVoieAdministration(?string $voieAdministration): static
    {
        $this->voieAdministration = $voieAdministration;

        return $this;
    }

    public function getEtatAutorisation(): ?string
    {
        return $this->etatAutorisation;
    }

    public function setEtatAutorisation(string $etatAutorisation): static
    {
        $this->etatAutorisation = $etatAutorisation;

        return $this;
    }

    public function getProcedure(): ?string
    {
        return $this->procedure;
    }

    public function setProcedure(string $procedure): static
    {
        $this->procedure = $procedure;

        return $this;
    }

    public function getEtatCommercialisation(): ?string
    {
        return $this->etatCommercialisation;
    }

    public function setEtatCommercialisation(string $etatCommercialisation): static
    {
        $this->etatCommercialisation = $etatCommercialisation;

        return $this;
    }

    public function getDateCommercialisation(): ?\DateTimeInterface
    {
        return $this->dateCommercialisation;
    }

    public function setDateCommercialisation(\DateTimeInterface $dateCommercialisation): static
    {
        $this->dateCommercialisation = $dateCommercialisation;

        return $this;
    }

    public function getFabricant(): ?string
    {
        return $this->fabricant;
    }

    public function setFabricant(?string $fabricant): static
    {
        $this->fabricant = $fabricant;

        return $this;
    }

    public function getTraitements()
    {
        return $this->traitements;
    }

    public function getInventaires()
    {
        return $this->inventaires;
    }
}

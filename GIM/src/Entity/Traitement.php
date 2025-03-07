<?php

namespace App\Entity;

use App\Repository\TraitementRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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

    /**
     * @var Collection<int, Medicament>
     */
    #[ORM\ManyToMany(targetEntity: Medicament::class, inversedBy: 'traitements')]
    private Collection $medicaments;

    public function __construct()
    {
        $this->medicaments = new ArrayCollection();
    }



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

    /**
     * @return Collection<int, Medicament>
     */
    public function getMedicaments(): Collection
    {
        return $this->medicaments;
    }

    public function addMedicament(Medicament $medicament): static
    {
        if (!$this->medicaments->contains($medicament)) {
            $this->medicaments->add($medicament);
        }

        return $this;
    }

    public function removeMedicament(Medicament $medicament): static
    {
        $this->medicaments->removeElement($medicament);

        return $this;
    }

    public function deduireMedicaments(): void
    {
        if ($this->actif) {
            foreach ($this->medicaments as $medicament) {
                $stockRestant = $medicament->getStock();
                $nouveauStock = max(0, $stockRestant - $this->dose); // Empêcher un stock négatif
                $medicament->setStock($nouveauStock);
            }
        }
    }

    /**
     * @return array
     */
    public function getJoursDePrise(): array
    {
        $nombreDePrisesParSemaine = $this->dose;
        $joursDePrise = [];

        $interval = 7 / $nombreDePrisesParSemaine;
        for ($i = 0; $i < $nombreDePrisesParSemaine; $i++) {
            $jour = round(1 + $i * $interval); 
            if ($jour > 7) {
                $jour = 7; 
            }
            $joursDePrise[] = $jour;
        }

        return $joursDePrise;
    }
}

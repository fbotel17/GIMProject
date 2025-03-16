<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\NotificationsRepository;

#[ORM\Entity(repositoryClass: NotificationsRepository::class)]
class Notifications
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: Traitement::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Traitement $traitement = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $dateNotification = null;

    // Getters & Setters
    public function getId(): ?int { return $this->id; }

    public function getUser(): ?User { return $this->user; }
    public function setUser(?User $user): self { $this->user = $user; return $this; }

    public function getTraitement(): ?Traitement { return $this->traitement; }
    public function setTraitement(?Traitement $traitement): self { $this->traitement = $traitement; return $this; }

    public function getDateNotification(): ?\DateTimeInterface { return $this->dateNotification; }
    public function setDateNotification(\DateTimeInterface $dateNotification): self { $this->dateNotification = $dateNotification; return $this; }
}

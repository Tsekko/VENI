<?php

namespace App\Entity;


use App\Repository\SortieRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SortieRepository::class)]
class Rechercher
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $query = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $debut = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $fin = null;

    #[ORM\Column(nullable: true)]
    private ?bool $checkbox_organisateur = null;

    #[ORM\Column(nullable: true)]
    private ?bool $checkbox_inscrit = null;

    #[ORM\Column(nullable: true)]
    private ?bool $checkbox_non_inscrit = null;

    #[ORM\Column(nullable: true)]
    private ?bool $checkbox_passes = null;

    #[ORM\Column(nullable: true)]
    private ?int $site = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuery(): ?string
    {
        return $this->query;
    }

    public function setQuery(?string $query): self
    {
        $this->query = $query;

        return $this;
    }

    public function getDebut(): ?\DateTimeInterface
    {
        return $this->debut;
    }

    public function setDebut(?\DateTimeInterface $debut): self
    {
        $this->debut = $debut;

        return $this;
    }

    public function getFin(): ?\DateTimeInterface
    {
        return $this->fin;
    }

    public function setFin(?\DateTimeInterface $fin): self
    {
        $this->fin = $fin;

        return $this;
    }

    public function isCheckboxOrganisateur(): ?bool
    {
        return $this->checkbox_organisateur;
    }

    public function setCheckboxOrganisateur(?bool $checkbox_organisateur): self
    {
        $this->checkbox_organisateur = $checkbox_organisateur;

        return $this;
    }

    public function isCheckboxInscrit(): ?bool
    {
        return $this->checkbox_inscrit;
    }

    public function setCheckboxInscrit(?bool $checkbox_inscrit): self
    {
        $this->checkbox_inscrit = $checkbox_inscrit;

        return $this;
    }

    public function isCheckboxNonInscrit(): ?bool
    {
        return $this->checkbox_non_inscrit;
    }

    public function setCheckboxNonInscrit(?bool $checkbox_non_inscrit): self
    {
        $this->checkbox_non_inscrit = $checkbox_non_inscrit;

        return $this;
    }

    public function isCheckboxPasses(): ?bool
    {
        return $this->checkbox_passes;
    }

    public function setCheckboxPasses(?bool $checkbox_passes): self
    {
        $this->checkbox_passes = $checkbox_passes;

        return $this;
    }

    public function getSite(): ?int
    {
        return $this->site;
    }

    public function setSite(?int $site): self
    {
        $this->site = $site;

        return $this;
    }
}

<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $name;

    #[ORM\Column(type: 'string', length: 255)]
    private $Cas;

    #[ORM\Column(type: 'string', length: 255)]
    private $formula;

    #[ORM\Column(type: 'decimal', precision: 11, scale: 6, nullable: true)]
    private $molecularMass;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getCas(): ?string
    {
        return $this->Cas;
    }

    public function setCas(string $Cas): self
    {
        $this->Cas = $Cas;

        return $this;
    }

    public function getFormula(): ?string
    {
        return $this->formula;
    }

    public function setFormula(string $formula): self
    {
        $this->formula = $formula;

        return $this;
    }

    

    public function setMolecularMass($molecularMass): self
    {
        $this->molecularMass = $molecularMass;

        return $this;
    }

    /**
     * Get the value of molecularMass
     */ 
    public function getMolecularMass()
    {
        return $this->molecularMass;
    }
}

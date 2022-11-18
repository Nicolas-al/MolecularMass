<?php

namespace App\Entity;

use App\Repository\AtomeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AtomeRepository::class)]
class Atome
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $name;

    #[ORM\Column(type: 'decimal', precision: 11, scale: 6)]
    private $mass;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function setMass($mass): self
    {
        $this->mass = $mass;

        return $this;
    }

    /**
     * Get the value of mass
     */ 
    public function getMass()
    {
        return $this->mass;
    }
}

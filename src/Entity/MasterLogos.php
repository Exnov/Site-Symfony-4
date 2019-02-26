<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MasterLogosRepository")
 */
class MasterLogos
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Logos", mappedBy="masterLogos", cascade={"persist", "remove"})
     */
    private $Logos;

    public function __construct()
    {
        $this->Logos = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection|Logos[]
     */
    public function getLogos(): Collection
    {
        return $this->Logos;
    }

    public function addLogo(Logos $logo): self
    {
        if (!$this->Logos->contains($logo)) {
            $this->Logos[] = $logo;
            $logo->setMasterLogos($this);
        }

        return $this;
    }

    public function removeLogo(Logos $logo): self
    {
        if ($this->Logos->contains($logo)) {
            $this->Logos->removeElement($logo);
            // set the owning side to null (unless already changed)
            if ($logo->getMasterLogos() === $this) {
                $logo->setMasterLogos(null);
            }
        }

        return $this;
    }
}

<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MasterBackgroundRepository")
 */
class MasterBackground
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Background", mappedBy="masterBackground", cascade={"persist", "remove"})
     */
    private $Backgrounds;

    public function __construct()
    {
        $this->Backgrounds = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection|Background[]
     */
    public function getBackgrounds(): Collection
    {
        return $this->Backgrounds;
    }

    public function addBackground(Background $background): self
    {
        if (!$this->Backgrounds->contains($background)) {
            $this->Backgrounds[] = $background;
            $background->setMasterBackground($this);
        }

        return $this;
    }

    public function removeBackground(Background $background): self
    {
        if ($this->Backgrounds->contains($background)) {
            $this->Backgrounds->removeElement($background);
            // set the owning side to null (unless already changed)
            if ($background->getMasterBackground() === $this) {
                $background->setMasterBackground(null);
            }
        }

        return $this;
    }
}

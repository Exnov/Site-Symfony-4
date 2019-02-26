<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\BackgroundRepository")
 */
class Background
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $wall;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $image;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\MasterBackground", inversedBy="Backgrounds")
     */
    private $masterBackground;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getWall(): ?string
    {
        return $this->wall;
    }

    public function setWall(string $wall): self
    {
        $this->wall = $wall;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getMasterBackground(): ?MasterBackground
    {
        return $this->masterBackground;
    }

    public function setMasterBackground(?MasterBackground $masterBackground): self
    {
        $this->masterBackground = $masterBackground;

        return $this;
    }
}

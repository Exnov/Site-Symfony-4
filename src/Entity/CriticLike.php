<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CriticLikeRepository")
 */
class CriticLike
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Critic", inversedBy="criticLikes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $critic;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="criticLikes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCritic(): ?Critic
    {
        return $this->critic;
    }

    public function setCritic(?Critic $critic): self
    {
        $this->critic = $critic;

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
}

<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CriticRepository")
 */
class Critic
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="critics")
     * @ORM\JoinColumn(nullable=false)
     */
    private $author;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $category;

    /**
     * @ORM\Column(type="text")
     */
    private $content;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="integer")
     */
    private $itemId;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\CriticSignalement", mappedBy="critic", orphanRemoval=true)
     */
    private $criticSignalements;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\CriticLike", mappedBy="critic", orphanRemoval=true)
     */
    private $criticLikes;

    //perso
    private $item;

    //perso
    private $route;

    public function __construct()
    {
        $this->criticSignalements = new ArrayCollection();
        $this->criticLikes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(string $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

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

    public function getItemId(): ?int
    {
        return $this->itemId;
    }

    public function setItemId(int $itemId): self
    {
        $this->itemId = $itemId;

        return $this;
    }

    /**
     * @return Collection|CriticSignalement[]
     */
    public function getCriticSignalements(): Collection
    {
        return $this->criticSignalements;
    }

    public function addCriticSignalement(CriticSignalement $criticSignalement): self
    {
        if (!$this->criticSignalements->contains($criticSignalement)) {
            $this->criticSignalements[] = $criticSignalement;
            $criticSignalement->setCritic($this);
        }

        return $this;
    }

    public function removeCriticSignalement(CriticSignalement $criticSignalement): self
    {
        if ($this->criticSignalements->contains($criticSignalement)) {
            $this->criticSignalements->removeElement($criticSignalement);
            // set the owning side to null (unless already changed)
            if ($criticSignalement->getCritic() === $this) {
                $criticSignalement->setCritic(null);
            }
        }

        return $this;
    }

    //perso : vérification si une critic contient des signalements
    public function hasSignalements(){
        if(!$this->getCriticSignalements()->isEmpty()){
            return true;
        }
        return false;
    }


    /**
     * @return Collection|CriticLike[]
     */
    public function getCriticLikes(): Collection
    {
        return $this->criticLikes;
    }

    public function addCriticLike(CriticLike $criticLike): self
    {
        if (!$this->criticLikes->contains($criticLike)) {
            $this->criticLikes[] = $criticLike;
            $criticLike->setCritic($this);
        }

        return $this;
    }

    public function removeCriticLike(CriticLike $criticLike): self
    {
        if ($this->criticLikes->contains($criticLike)) {
            $this->criticLikes->removeElement($criticLike);
            // set the owning side to null (unless already changed)
            if ($criticLike->getCritic() === $this) {
                $criticLike->setCritic(null);
            }
        }

        return $this;
    }


    //perso : permet de savoir si critic est "liké" par un utilisateur
    public function isLikedByUser(User $user) : bool
    {
        foreach ($this->criticLikes as $like) {

            if($like->getUser()===$user){
                return true;
            }          
        }
         return false;
    }    

    //perso
    public function setItem($item){
        $this->item=$item;
    }    
     
    public function getItem(){
        return $this->item;
    }

    //perso
    public function setRoute($route){
        $this->route=$route;
    }    
     
    public function getRoute(){
        return $this->route;
    }    

    //----------
}

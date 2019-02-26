<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ReactionRepository")
 */
class Reaction
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="reactions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $author;

    /**
     * @ORM\Column(type="text")
     */
    private $content;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Topic", inversedBy="reactions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $topic;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Signalement", mappedBy="reaction", orphanRemoval=true)
     */
    private $signalements;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ReactionLike", mappedBy="reaction", orphanRemoval=true)
     */
    private $reactionLikes;

    public function __construct()
    {
        $this->signalements = new ArrayCollection();
        $this->reactionLikes = new ArrayCollection();
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

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getTopic(): ?Topic
    {
        return $this->topic;
    }

    public function setTopic(?Topic $topic): self
    {
        $this->topic = $topic;

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

    /**
     * @return Collection|Signalement[]
     */
    public function getSignalements(): Collection
    {
        return $this->signalements;
    }

    public function addSignalement(Signalement $signalement): self
    {
        if (!$this->signalements->contains($signalement)) {
            $this->signalements[] = $signalement;
            $signalement->setReaction($this);
        }

        return $this;
    }

    public function removeSignalement(Signalement $signalement): self
    {
        if ($this->signalements->contains($signalement)) {
            $this->signalements->removeElement($signalement);
            // set the owning side to null (unless already changed)
            if ($signalement->getReaction() === $this) {
                $signalement->setReaction(null);
            }
        }

        return $this;
    }

    //perso : vérification si une reaction contient des signalements
    public function hasSignalements(){

        if(!$this->getSignalements()->isEmpty()){
            return true;
        }

        return false;
    }

    /**
     * @return Collection|ReactionLike[]
     */
    public function getReactionLikes(): Collection
    {
        return $this->reactionLikes;
    }

    public function addReactionLike(ReactionLike $reactionLike): self
    {
        if (!$this->reactionLikes->contains($reactionLike)) {
            $this->reactionLikes[] = $reactionLike;
            $reactionLike->setReaction($this);
        }

        return $this;
    }

    public function removeReactionLike(ReactionLike $reactionLike): self
    {
        if ($this->reactionLikes->contains($reactionLike)) {
            $this->reactionLikes->removeElement($reactionLike);
            // set the owning side to null (unless already changed)
            if ($reactionLike->getReaction() === $this) {
                $reactionLike->setReaction(null);
            }
        }

        return $this;
    }

    //perso : permet de savoir si ce reaction (message dans forum) est "liké" par un utilisateur
    public function isLikedByUser(User $user) : bool
    {
        foreach ($this->reactionLikes as $like) {

            if($like->getUser()===$user){
                return true;
            }          
        }
         return false;
    }        
 
}

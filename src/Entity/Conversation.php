<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ConversationRepository")
 */
class Conversation
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\User", inversedBy="conversations")
     */
    private $member;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Message", mappedBy="conversation", orphanRemoval=true)
     */
    private $messages;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Corbeille", mappedBy="conversation", orphanRemoval=true)
     */
    private $corbeilles;



    public function __construct()
    {
        $this->member = new ArrayCollection();
        $this->messages = new ArrayCollection();
        $this->corbeilles = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection|User[]
     */
    public function getMembers(): Collection
    {
        return $this->member;
    }

    public function addMember(User $member): self
    {
        if (!$this->member->contains($member)) {
            $this->member[] = $member;
        }

        return $this;
    }

    public function removeMember(User $member): self
    {
        if ($this->member->contains($member)) {
            $this->member->removeElement($member);
        }

        return $this;
    }

    /**
     * @return Collection|Message[]
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(Message $message): self
    {
        if (!$this->messages->contains($message)) {
            $this->messages[] = $message;
            $message->setConversation($this);
        }

        return $this;
    }

    public function removeMessage(Message $message): self
    {
        if ($this->messages->contains($message)) {
            $this->messages->removeElement($message);
            // set the owning side to null (unless already changed)
            if ($message->getConversation() === $this) {
                $message->setConversation(null);
            }
        }

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
     * @return Collection|Corbeille[]
     */
    public function getCorbeilles(): Collection
    {
        return $this->corbeilles;
    }

    public function addCorbeille(Corbeille $corbeille): self
    {
        if (!$this->corbeilles->contains($corbeille)) {
            $this->corbeilles[] = $corbeille;
            $corbeille->setConversation($this);
        }

        return $this;
    }

    public function removeCorbeille(Corbeille $corbeille): self
    {
        if ($this->corbeilles->contains($corbeille)) {
            $this->corbeilles->removeElement($corbeille);
            // set the owning side to null (unless already changed)
            if ($corbeille->getConversation() === $this) {
                $corbeille->setConversation(null);
            }
        }

        return $this;
    }

    //perso : savoir si une conversation est dans corbeille de l'user
    public function isInCorbeille(User $user){

        $corbeilles=$this->getCorbeilles();
        foreach ($corbeilles as $corbeille) {
            
            if($this===$corbeille->getConversation() && $user===$corbeille->getUser()){
                return true;
            }

        }
        return false;
    }

    //perso : savoir si tous les membres d'une conversation l'ont jetée dans la corbeille, et donc si la conversation est à supprimer de la bdd
    public function isReadyToDisappear(){

        $membres=$this->getMembers();
        $nMembres=count($membres);

        foreach ($membres as $membre) {

            if($this->isInCorbeille($membre)){ //à chaque membre trouvé on décrémente de 1 le total de membres
                dump("oui pour "." ".$membre->getUsername());
                $nMembres-=1;
            }
        }

        if($nMembres>0){
            return false;
        }

        return true;
    }

       
    //----------
}

<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

use App\Service\Assistant;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ForumRepository")
 * @UniqueEntity(
 *  fields={"category"},
 *  message="Cette catégorie existe déjà" 
 * ) 
 */
class Forum
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
    private $category;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $slug;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Topic", mappedBy="category", orphanRemoval=true)
     */
    private $topics;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdAt;


    public function __construct()
    {
        $this->topics = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    //perso : création de slug à partir d'une category
    public function createSlug(string $category){

        $transliterator = \Transliterator::create(
            'NFD; [:Nonspacing Mark:] Remove; NFC;'
        );
        $slug=$transliterator->transliterate($category);

        //on remplace les espaces par des tirets          
        $transliterator = \Transliterator::createFromRules(
            "::Latin-ASCII; ::Lower; [^[:L:][:N:]]+ > '-';"
        );

        $slug=trim($transliterator->transliterate($slug),'-');

        return $slug;
    }

    /**
     * @return Collection|Topic[]
     */
    public function getTopics(): Collection
    {
        return $this->topics;
    }

    public function addTopic(Topic $topic): self
    {
        if (!$this->topics->contains($topic)) {
            $this->topics[] = $topic;
            $topic->setCategory($this);
        }

        return $this;
    }

    public function removeTopic(Topic $topic): self
    {
        if ($this->topics->contains($topic)) {
            $this->topics->removeElement($topic);
            // set the owning side to null (unless already changed)
            if ($topic->getCategory() === $this) {
                $topic->setCategory(null);
            }
        }

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    //perso : compte le nombre de reactions dans une rubrique
    public function countReactions(){
        $topics=$this->getTopics();
        $nReactions=0;
        foreach ($topics as $topic) {
            $reactions=$topic->getReactions();
            $nReactions+=count($reactions);
        }
        return $nReactions;
    }

    //perso : recupère le dernier message écrit dans la rubrique
     public function getLastReaction(){
        $topics=$this->getTopics();
        $reactions=[];
        foreach ($topics as $topic) {
            $allReactions=$topic->getReactions();
            foreach ($allReactions as $reaction) {
                $reactions[]=$reaction;
            }
        }

        $assistant= new Assistant();
        $reactionsOrdonne=$assistant->orderByDateDesc($reactions);
 
        if(count($reactionsOrdonne)>0){
            return $reactionsOrdonne[0];
        }

        return null;
    }  

    //----------- 
}

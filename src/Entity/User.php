<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;


/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity(
 *  fields={"email"},
 *  message="L'émail que vous avez indiqué est déjà utilisé"
 * )
  * @UniqueEntity(
 *  fields={"username"},
 *  message="Le nom d'utilisateur que vous avez indiqué est déjà utilisé"
 * )
 */
class User implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Email()
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(min=8, minMessage="Votre mot de passe doit faire au minimun 8 caractères")
     */
    private $password;

    /**
    * @Assert\EqualTo(propertyPath="password", message="Vous n'avez pas tapé le même mot de passe")
    */
    public $confirm_password;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Topic", mappedBy="author", orphanRemoval=true)
     */
    private $topics;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Reaction", mappedBy="author", orphanRemoval=true)
     */
    private $reactions;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Conversation", mappedBy="member")
     */
    private $conversations;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Message", mappedBy="author", orphanRemoval=true)
     */
    private $messages;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Message", mappedBy="target")
     */
    private $messages_target;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Signalement", mappedBy="user", orphanRemoval=true)
     */
    private $signalements;

    /**
     * @ORM\Column(type="json")
     */
    private $roles;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ReactionLike", mappedBy="user", orphanRemoval=true)
     */
    private $reactionLikes;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\UserImage", mappedBy="user", cascade={"persist", "remove"})
     */
    private $userImage;

    //sans table associée, ajout perso pour form ProfilType
    /**   
     * @Assert\File(mimeTypes={ "image/jpeg" })     
     */
    private $image;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\View", mappedBy="viewer", orphanRemoval=true)
     */
    private $views;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Corbeille", mappedBy="user", orphanRemoval=true)
     */
    private $corbeilles;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\News", mappedBy="author", orphanRemoval=true)
     */
    private $news;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Film", mappedBy="author", orphanRemoval=true)
     */
    private $films;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Music", mappedBy="author", orphanRemoval=true)
     */
    private $musics;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Bio", mappedBy="author", orphanRemoval=true)
     */
    private $bios;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Critic", mappedBy="author", orphanRemoval=true)
     */
    private $critics;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\CriticSignalement", mappedBy="user", orphanRemoval=true)
     */
    private $criticSignalements;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\CriticLike", mappedBy="user", orphanRemoval=true)
     */
    private $criticLikes;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Mentions", mappedBy="author", orphanRemoval=true)
     */
    private $mentions;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\UserBye", mappedBy="user", cascade={"persist", "remove"})
     */
    private $userBye;



    public function __construct()
    {
        $this->topics = new ArrayCollection();
        $this->reactions = new ArrayCollection();
        $this->conversations = new ArrayCollection();
        $this->messages = new ArrayCollection();
        $this->messages_target = new ArrayCollection();
        $this->signalements = new ArrayCollection();
        $this->reactionLikes = new ArrayCollection();
        $this->views = new ArrayCollection();
        $this->corbeilles = new ArrayCollection();
        $this->news = new ArrayCollection();
        $this->films = new ArrayCollection();
        $this->musics = new ArrayCollection();
        $this->bios = new ArrayCollection();
        $this->critics = new ArrayCollection();
        $this->criticSignalements = new ArrayCollection();
        $this->criticLikes = new ArrayCollection();
        $this->mentions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function eraseCredentials(){}

    public function getSalt(){}

    //----

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
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
            $topic->setAuthor($this);
        }

        return $this;
    }

    public function removeTopic(Topic $topic): self
    {
        if ($this->topics->contains($topic)) {
            $this->topics->removeElement($topic);
            // set the owning side to null (unless already changed)
            if ($topic->getAuthor() === $this) {
                $topic->setAuthor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Reaction[]
     */
    public function getReactions(): Collection
    {
        return $this->reactions;
    }

    public function addReaction(Reaction $reaction): self
    {
        if (!$this->reactions->contains($reaction)) {
            $this->reactions[] = $reaction;
            $reaction->setAuthor($this);
        }

        return $this;
    }

    public function removeReaction(Reaction $reaction): self
    {
        if ($this->reactions->contains($reaction)) {
            $this->reactions->removeElement($reaction);
            // set the owning side to null (unless already changed)
            if ($reaction->getAuthor() === $this) {
                $reaction->setAuthor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Conversation[]
     */
    public function getConversations(): Collection
    {
        return $this->conversations;
    }

    public function addConversation(Conversation $conversation): self
    {
        if (!$this->conversations->contains($conversation)) {
            $this->conversations[] = $conversation;
            $conversation->addMember($this);
        }

        return $this;
    }

    public function removeConversation(Conversation $conversation): self
    {
        if ($this->conversations->contains($conversation)) {
            $this->conversations->removeElement($conversation);
            $conversation->removeMember($this);
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
            $message->setAuthor($this);
        }

        return $this;
    }

    public function removeMessage(Message $message): self
    {
        if ($this->messages->contains($message)) {
            $this->messages->removeElement($message);
            // set the owning side to null (unless already changed)
            if ($message->getAuthor() === $this) {
                $message->setAuthor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Message[]
     */
    public function getMessagesTarget(): Collection
    {
        return $this->messages_target;
    }

    public function addMessagesTarget(Message $messagesTarget): self
    {
        if (!$this->messages_target->contains($messagesTarget)) {
            $this->messages_target[] = $messagesTarget;
            $messagesTarget->addTarget($this);
        }

        return $this;
    }

    public function removeMessagesTarget(Message $messagesTarget): self
    {
        if ($this->messages_target->contains($messagesTarget)) {
            $this->messages_target->removeElement($messagesTarget);
            $messagesTarget->removeTarget($this);
        }

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
            $signalement->setUser($this);
        }

        return $this;
    }

    public function removeSignalement(Signalement $signalement): self
    {
        if ($this->signalements->contains($signalement)) {
            $this->signalements->removeElement($signalement);
            // set the owning side to null (unless already changed)
            if ($signalement->getUser() === $this) {
                $signalement->setUser(null);
            }
        }

        return $this;
    }

    //perso
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

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
            $reactionLike->setUser($this);
        }

        return $this;
    }

    public function removeReactionLike(ReactionLike $reactionLike): self
    {
        if ($this->reactionLikes->contains($reactionLike)) {
            $this->reactionLikes->removeElement($reactionLike);
            // set the owning side to null (unless already changed)
            if ($reactionLike->getUser() === $this) {
                $reactionLike->setUser(null);
            }
        }

        return $this;
    }

    public function getUserImage(): ?UserImage
    {
        return $this->userImage;
    }

    public function setUserImage(UserImage $userImage): self
    {
        $this->userImage = $userImage;

        // set the owning side of the relation if necessary
        if ($this !== $userImage->getUser()) {
            $userImage->setUser($this);
        }

        return $this;
    }

    //perso : vérification si un user a un avatar
    public function hasUserImage(){

        if(!empty($this->getUserImage())){
            return true;
        }

        return false;
    }     

    //perso : suppression d'un userImage
    public function removeUserImage(UserImage $userImage): self
    {
        // set the owning side to null (unless already changed)
        if ($userImage->getUser() === $this) {
            $this->userImage = null;
        }
    
        return $this;
    }

    //sans table associée, ajout perso pour form ProfilType
    public function getImage() 
    {
        return $this->image;
    }

    public function setImage( $image)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * @return Collection|View[]
     */
    public function getViews(): Collection
    {
        return $this->views;
    }

    public function addView(View $view): self
    {
        if (!$this->views->contains($view)) {
            $this->views[] = $view;
            $view->setViewer($this);
        }

        return $this;
    }

    public function removeView(View $view): self
    {
        if ($this->views->contains($view)) {
            $this->views->removeElement($view);
            // set the owning side to null (unless already changed)
            if ($view->getViewer() === $this) {
                $view->setViewer(null);
            }
        }

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
            $corbeille->setUser($this);
        }

        return $this;
    }

    public function removeCorbeille(Corbeille $corbeille): self
    {
        if ($this->corbeilles->contains($corbeille)) {
            $this->corbeilles->removeElement($corbeille);
            // set the owning side to null (unless already changed)
            if ($corbeille->getUser() === $this) {
                $corbeille->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|News[]
     */
    public function getNews(): Collection
    {
        return $this->news;
    }

    public function addNews(News $news): self
    {
        if (!$this->news->contains($news)) {
            $this->news[] = $news;
            $news->setAuthor($this);
        }

        return $this;
    }

    public function removeNews(News $news): self
    {
        if ($this->news->contains($news)) {
            $this->news->removeElement($news);
            // set the owning side to null (unless already changed)
            if ($news->getAuthor() === $this) {
                $news->setAuthor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Film[]
     */
    public function getFilms(): Collection
    {
        return $this->films;
    }

    public function addFilm(Film $film): self
    {
        if (!$this->films->contains($film)) {
            $this->films[] = $film;
            $film->setAuthor($this);
        }

        return $this;
    }

    public function removeFilm(Film $film): self
    {
        if ($this->films->contains($film)) {
            $this->films->removeElement($film);
            // set the owning side to null (unless already changed)
            if ($film->getAuthor() === $this) {
                $film->setAuthor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Music[]
     */
    public function getMusics(): Collection
    {
        return $this->musics;
    }

    public function addMusic(Music $music): self
    {
        if (!$this->musics->contains($music)) {
            $this->musics[] = $music;
            $music->setAuthor($this);
        }

        return $this;
    }

    public function removeMusic(Music $music): self
    {
        if ($this->musics->contains($music)) {
            $this->musics->removeElement($music);
            // set the owning side to null (unless already changed)
            if ($music->getAuthor() === $this) {
                $music->setAuthor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Bio[]
     */
    public function getBios(): Collection
    {
        return $this->bios;
    }

    public function addBio(Bio $bio): self
    {
        if (!$this->bios->contains($bio)) {
            $this->bios[] = $bio;
            $bio->setAuthor($this);
        }

        return $this;
    }

    public function removeBio(Bio $bio): self
    {
        if ($this->bios->contains($bio)) {
            $this->bios->removeElement($bio);
            // set the owning side to null (unless already changed)
            if ($bio->getAuthor() === $this) {
                $bio->setAuthor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Critic[]
     */
    public function getCritics(): Collection
    {
        return $this->critics;
    }

    public function addCritic(Critic $critic): self
    {
        if (!$this->critics->contains($critic)) {
            $this->critics[] = $critic;
            $critic->setAuthor($this);
        }

        return $this;
    }

    public function removeCritic(Critic $critic): self
    {
        if ($this->critics->contains($critic)) {
            $this->critics->removeElement($critic);
            // set the owning side to null (unless already changed)
            if ($critic->getAuthor() === $this) {
                $critic->setAuthor(null);
            }
        }

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
            $criticSignalement->setUser($this);
        }

        return $this;
    }

    public function removeCriticSignalement(CriticSignalement $criticSignalement): self
    {
        if ($this->criticSignalements->contains($criticSignalement)) {
            $this->criticSignalements->removeElement($criticSignalement);
            // set the owning side to null (unless already changed)
            if ($criticSignalement->getUser() === $this) {
                $criticSignalement->setUser(null);
            }
        }

        return $this;
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
            $criticLike->setUser($this);
        }

        return $this;
    }

    public function removeCriticLike(CriticLike $criticLike): self
    {
        if ($this->criticLikes->contains($criticLike)) {
            $this->criticLikes->removeElement($criticLike);
            // set the owning side to null (unless already changed)
            if ($criticLike->getUser() === $this) {
                $criticLike->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Mentions[]
     */
    public function getMentions(): Collection
    {
        return $this->mentions;
    }

    public function addMention(Mentions $mention): self
    {
        if (!$this->mentions->contains($mention)) {
            $this->mentions[] = $mention;
            $mention->setAuthor($this);
        }

        return $this;
    }

    public function removeMention(Mentions $mention): self
    {
        if ($this->mentions->contains($mention)) {
            $this->mentions->removeElement($mention);
            // set the owning side to null (unless already changed)
            if ($mention->getAuthor() === $this) {
                $mention->setAuthor(null);
            }
        }

        return $this;
    }

    public function getUserBye(): ?UserBye
    {
        return $this->userBye;
    }

    public function setUserBye(UserBye $userBye): self
    {
        $this->userBye = $userBye;

        // set the owning side of the relation if necessary
        if ($this !== $userBye->getUser()) {
            $userBye->setUser($this);
        }

        return $this;
    }



}

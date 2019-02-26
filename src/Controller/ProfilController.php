<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use App\Repository\TopicRepository;
use App\Repository\ReactionRepository;
use App\Entity\Reaction;
use App\Repository\CriticRepository;
use App\Entity\Critic;
use App\Repository\NewsRepository;
use App\Repository\FilmRepository;
use App\Repository\MusicRepository;
use App\Form\ProfilType;

use App\Entity\Conversation;
use App\Repository\ConversationRepository;
use App\Entity\Message;
use App\Form\MessageType;
use App\Repository\MessageRepository;
use App\Form\WordpassType;

use App\Entity\User;
use App\Entity\View;
use App\Entity\Corbeille;
use App\Entity\UserBye; //autorm user

use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use  Knp\Component\Pager\PaginatorInterface;

use Symfony\Component\HttpFoundation\File\Exception\FileException; //avatar
use App\Service\FileUploader; //avatar
use App\Entity\UserImage; //avatar

use  App\Service\Assistant; //regroupe différentes fonctions partagées par les controllers


class ProfilController extends AbstractController{

	//tab 'infos', tab principal
    /**
     * @Route("/profil", name="profil")
     */
    public function index(Request $request, ObjectManager $manager, FileUploader $fileUploader){

    	$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED'); 

    	$user = $this->getUser();

    	//--tab "Infos"
    	//on crée notre form, et on le relie à la classe user
    	$form=$this->createForm(ProfilType::class,$user);
    	//on demande au form d'analyser la requete reçu, cad $request      
    	$form->handleRequest($request);
       
    	if($form->isSubmitted() && $form->isValid()){

            //suppression de l'avatar précédemment enregistré; avec la checkbox
            //si case 'Supprimer l'avatar' cochée et que l'user n'a pas ajouté un nouvel avatar, on supprime l'avatar précédemment enregistré:
            if($request->request->get('supp-avatar-saved')!==null && empty($request->files->get('profil')['image'])){

                /*
                on supprime le userImage de l'user :
                - dans la bdd, et dans l'entité de l'user
                - on ne verifie pas ici si l'user a déjà une image enregistrée (si case cochée existe, c'est parce qu'il a une image enregistrée).
                */
                $userImage=$user->getUserImage();
                $user->removeUserImage($userImage);

                $manager->remove($userImage);
                $manager->flush(); 
            }

            /*
            avatar :
            - $file = $user->getImage(); //fonction de user ajoutée, sans table dans bdd, permet de créer 
            l'objet userImage avec les références de l'image de l'user
            - détournement pour résoudre un problème de récupération des ref de l'image par $user; on passe directement par $request 
            - si case 'Supprimer l'avatar' non cochée et que l'user a ajoutée un nouvel avatar, on maj l'avatar
            */
            if($request->request->get('supp-avatar-saved')===null && !empty($request->files->get('profil')['image'])){
            
                $file =$request->files->get('profil')['image'];
                $fileName = $fileUploader->uploadImage($file);
                $fileUploader->resize(140,140);
                $thumbnail=$fileUploader->getThumbnailName();

                //on vérifie si l'user a déjà une image enregistrée
                $userImage=new UserImage();  
                if($user->hasUserImage()){
                    $userImage=$user->getUserImage();
                }                         
                $userImage->setUser($user);
                $userImage->setImage($fileName);
                $userImage->setThumbnail($thumbnail);              
                
                $manager->persist($userImage);
                $manager->flush();
                             
                //on ajoute les réferences de l'image à l'user 
                $user->setUserImage($userImage);
            }
         
    		$manager->persist($user);
    		$manager->flush();                     
    	} 	 

        //-------
        //avatar affichage 
        $userImage=$user->getUserImage();
        //-------          	

        return $this->render('profil/index.html.twig', [
            'profilForm' => $form->createView(),
            'userImage' => $userImage,       
            'titlePage'=> 'Espace privé de ',      
        ]);
    }

    //tab 'Mot de passe', tab password
    /**
     * @Route("/profil/password", name="profil_password")
     */
    public function password(Request $request, ObjectManager $manager, UserPasswordEncoderInterface $encoder){

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED'); 

        $user = $this->getUser();

        //--tab "Infos"
        //on crée notre form, et on le relie à la classe user
        $form=$this->createForm(WordpassType::class,$user);

        //on demande au form d'analyser la requete reçu, cad $request      
        $form->handleRequest($request);
       
        if($form->isSubmitted() && $form->isValid()){

            $hash=$encoder->encodePassword($user,$user->getPassword());
            $user->setPassword($hash);

            $manager->persist($user);
            $manager->flush();
        }        

        //-------
        //avatar affichage 
        $userImage=$user->getUserImage();
        //------- 

        return $this->render('profil/password.html.twig', [
            'passwordForm' => $form->createView(),
            'userImage' => $userImage, 
            'titlePage'=> 'Espace privé de ',           
        ]);
    }

    //tab 'sujets'
    /**
     * @Route("/profil/sujets", name="profil_topics")
     */
    public function topics (TopicRepository $repo, Request $request, PaginatorInterface $paginator, Assistant $assistant){

    	$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED'); 

    	$user = $this->getUser();

    	//--tab "Sujets"
        $nbTopics=count($repo->findBy(['author'=>$user]));

        $topicsRecup=$repo->findBy(['author'=>$user]);
        $topicsRecup=$assistant->orderByDateDesc($topicsRecup); 

        $topics = $paginator->paginate(
            $topicsRecup, 
            $request->query->getInt('page', 1)/*page number*/,
            5/*limit per page*/
        );   

         //-------
        //avatar affichage 
        $userImage=$user->getUserImage();
        //-------         	

        return $this->render('profil/topics.html.twig', [
    		'topics' => $topics,
            'userImage' => $userImage,  
            'nbTopicsTotal'=>$nbTopics,
            'titlePage'=> 'Espace privé de ',
        ]);

     }

    //tab 'commentaires forum'
    /**
     * @Route("/profil/commentaires", name="profil_comments")
     */
    public function comments (ReactionRepository $repo, Request $request, PaginatorInterface $paginator, Assistant $assistant){

    	$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED'); 

    	$user = $this->getUser();

        $nbComments=count($repo->findBy(['author'=>$user]));      

        $commentsRecup=$repo->findBy(['author'=>$user]);
        $commentsRecup=$assistant->orderByDateDesc($commentsRecup);  

        $reactions = $paginator->paginate(
            $commentsRecup,
            $request->query->getInt('page', 1)/*page number*/,
            5/*limit per page*/
        );

         //-------
        //avatar affichage 
        $userImage=$user->getUserImage();
        //-------          

        return $this->render('profil/comments.html.twig', [
    		'reactions' => $reactions,
            'userImage' => $userImage,  
            'nbCommentsTotal'=>$nbComments,
            'titlePage'=> 'Espace privé de ',
        ]);

     } 

    //remove comments de forum 
    /**
     * @Route("/profil/commentaire/{id<\d+>}/suppression", name="profil_comment_remove")
     */
    public function removeComment(Reaction $reaction, ObjectManager $manager){

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED'); 

        $manager->remove($reaction);
        $manager->flush();

        return $this->redirectToRoute('profil_comments');      

    }


    //tab 'commentaires contents : News, Films, Music'
    /**
     * @Route("/profil/commentaires-contenus", name="profil_comments_contents")
     */
    public function commentsContents (CriticRepository $repoCritic, Request $request, PaginatorInterface $paginator, Assistant $assistant, NewsRepository $repoNews, FilmRepository $repoFilm, MusicRepository $repoMusic){

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED'); 

        $user = $this->getUser();

        $nbComments=count($repoCritic->findBy(['author'=>$user]));      

        $commentsRecup=$repoCritic->findBy(['author'=>$user]);
        $commentsRecup=$assistant->orderByDateDesc($commentsRecup);  

        //----------
        foreach ($commentsRecup as $comment) {
            
            $item='';
            $route='';
            $repo='';
            switch ($comment->getCategory()) {
                case 'news':
                    $repo=$repoNews;
                    $route="news_show";
                    break;
                case 'film':
                    $repo=$repoFilm;
                    $route="film_show";
                    break;
                case 'music':
                    $repo=$repoMusic;
                    $route="music_show";
                    break;                                        
            }
            $item=$repo->findOneBy(['id'=>$comment->getItemId()]);
            $comment->setItem($item);
            $comment->setRoute($route);
        }
        //---------

        $critics = $paginator->paginate(
            $commentsRecup,
            $request->query->getInt('page', 1)/*page number*/,
            5/*limit per page*/
        );

         //-------
        //avatar affichage 
        $userImage=$user->getUserImage();
        //-------          

        return $this->render('profil/comments_contents.html.twig', [
            'critics' => $critics,
            'userImage' => $userImage,  
            'nbCommentsTotal'=>$nbComments,
            'titlePage'=> 'Espace privé de ',
        ]);
    }       

    //remove comments de contents
    /**
     * @Route("/profil/commentaire-contenus/{id<\d+>}/suppression", name="profil_comment_contents_remove")
     */
    public function removeCommentContents(critic $critic, ObjectManager $manager){

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED'); 

        $manager->remove($critic);
        $manager->flush();

        return $this->redirectToRoute('profil_comments_contents');      
    }

    /**
     * @Route("/profil/mail/reception", name="profil_mail_reception")
     */
    public function mailReception(MessageRepository $repo, Request $request, PaginatorInterface $paginator){

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED'); 

        $user = $this->getUser();

        //on recupère les messages où l'user est la target
        //getMessagesTarget() permet via User de recupérer les messages où $user est la cible (destinataire)
        $messages=$user->getMessagesTarget();

        //-------
        $mailsRecup=$this->getMails($messages,$user);
        $nbMails=count($mailsRecup);

        $mails = $paginator->paginate(
            $mailsRecup, 
            $request->query->getInt('page', 1)/*page number*/,
            10/*limit per page*/
        );  

        //Récupération du nombre de messages à lire--
        $nbMailsRead=$this->getMailsRead($mails,$user);
        $nbMailsToRead=$nbMails-$nbMailsRead;
        $infosNbMails=$nbMailsToRead.' message(s) à lire sur '.$nbMails. ' message(s) reçu(s)';

        //-------
        //avatar affichage 
        $userImage=$user->getUserImage();
        //-------  

        return $this->render('profil/mail.html.twig',[   
            'messages'=>$mails, 
            'userImage' => $userImage,    
            'titlePage'=> 'Messagerie de ', 
            'infosNbMails'=>$infosNbMails, 
        ]);
    }


    /**
     * @Route("/profil/mail/expedition", name="profil_mail_expedition")
     */
    public function mailExpedition(MessageRepository $repo, Request $request, PaginatorInterface $paginator){

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        $user = $this->getUser();

        //on recupère les messages où l'user est l'auteur
        //getMessages() permet via User de recupérer les messages où $user est la l'auteur
        $messages=$user->getMessages();

        $mailsRecup=$this->getMails($messages,$user);
        $nbMails=count($mailsRecup);
        //--
 
        $mails = $paginator->paginate(
            $mailsRecup, 
            $request->query->getInt('page', 1)/*page number*/,
            10/*limit per page*/
        );      

        //-------
        //avatar affichage 
        $userImage=$user->getUserImage();
        //-------  

        return $this->render('profil/mail-send.html.twig',[  
            'messages'=>$mails,           
            'userImage' => $userImage,       
            'titlePage'=> 'Messagerie de ', 
            'infosNbMails'=>$nbMails. ' message(s) envoyé(s)',
        ]);
    }


    // --------------------------------------------------------------------------------------
    //fonction de suppression de toutes les conversations d'un user (où auteur et target, on met tout dans la corbeille)
    /**
     * @Route("/profil/mail/suppression-totale", name="profil_conversations_all_remove")
     */
     public function removeAllMessages(ConversationRepository $repo, Request $request, ObjectManager $manager){

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED'); 
        $user = $this->getUser();

        //on met toutes les conversations de l'user dans corbeille
        //supprime toutes les conversations de l'user (positions d'auteur et de cible confondues)   
        foreach ($user->getConversations() as $conversation) {
            $corbeille=new Corbeille();
            $corbeille->setUser($user)
                    ->setConversation($conversation)
                    ->setCreatedAt(new \Datetime())
            ;
            $manager->persist($corbeille);
            $manager->flush();
        }
        
        return $this->redirectToRoute('profil_mail_reception');
    }   
    // --------------------------------------------------------------------------------------


    //page de création d'un message privée :
    /**
     * @Route("/profil/messagerie/nouveau", name="profil_conversations_create")
     */
    public function create(Message $message = null, Request $request, ObjectManager $manager, ConversationRepository $repo){

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED'); 

        $user = $this->getUser();

        if(!$message){
            $message=new Message();
        }
        //on crée notre form, et on le relie à la classe Message
        $form=$this->createForm(MessageType::class,$message);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

           //on crée la conversation, et on l'enregistre dans la bdd
            $conversation=new Conversation();
            $conversation->setCreatedAt(new \Datetime());

            $manager->persist($conversation);
            $manager->flush();
            //--
            //on la recupère depuis la bdd pour la fournir au message créé
            $conversation=$repo->findLastConversation()[0];
        
            $message->setAuthor($user)
                    ->setConversation($conversation)
                    ->setCreatedAt(new \Datetime());
            ;

            //on enregistre le nouveau message dans la bdd
            $manager->persist($message);
            $manager->flush();

            //on compléte les données de la conversation pour l'enregistrer complétée dans la bdd
            $conversation->addMessage($message);
            $conversation->addMember($user); //on ajoute l'auteur du message
            foreach ($message->getTarget() as $target) { //on ajoute le(s) destinataire(s) du message
                $conversation->addMember($target);
            }

            $manager->persist($conversation);
            $manager->flush();

            return $this->redirectToRoute('profil_mail_reception');
        }

        return $this->render('profil/create.html.twig',[
            'messageForm' => $form->createView()
        ]);           
     }

    //page d'affichage d'un message privé :
    /**
     * @Route("/profil/messagerie/{id<\d+>}", name="profil_conversations_show")
     */
    public function show(Conversation $conversation, Request $request, ObjectManager $manager, Assistant $assistant){

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED'); 

        $user = $this->getUser();
        $message =new Message();

        //on crée notre form, et on le relie à la classe Message
        $form=$this->createForm(MessageType::class,$message);
        $form->handleRequest($request);

        /*
        filtrer la conversation : on ne garde dans la conversation que les messages où l'user est l'author ou la target; il n'a pas à voir les échanges dans la conversation où il n'est pas sollicité
        */
        $messages=[];
        foreach ($conversation->getMessages() as $m) { //pour les messages dans la conversation
            //on garde les messages où l'user courant est l'auteur (et aussi auteur et target en même temps)
            if($user->getId()==$m->getAuthor()->getId()){
                $messages[]=$m;
            }
            //et si l'user est seulement target
            else{
                $targets=$m->getTarget();
                foreach ($targets as $target) { //pour toutes les cibles dans le message
                    if($user->getId()==$target->getId()){
                         $messages[]=$m; //dès qu'on trouve l'user courant, au casse la boucle
                         break;
                    }
                }
            }
        }

        //----------------
        //classement des messages de la conversation du plus récent au plus vieux :
        $messages=$assistant->orderByDateDesc($messages); 

        //on vérifie au préalable que le message n'est pas déjà lu par l'user :
        $alreadyRead=$messages[0]->hasViewer($user); //renvoie true si message déjà vu et false sinon
        //si message pas encore lu, on vérifie si l'user fait partie des targets pour le passer en lu
        if($alreadyRead==false){

            foreach ($messages[0]->getTarget() as $target) { //on compare l'user à la ou les targets du message
                if($target==$user){ //si correspondance le message passe en lu
                    //on crée et enregistre un objet View qui associera l'user au message lu (User et Message sont automatiquement associés alors)
                    $view =new View();
                    $view->setViewer($user)
                         ->setMessage($messages[0])
                         ->setCreatedAt(new \Datetime());
                    ;
                    $manager->persist($view);
                    $manager->flush();
                }
            }
        }
        //---------------------------------------------------------------------------

        $idAuthorLastMessage=strval($messages[0]->getAuthor()->getId()); 
        $titleLastMessage=$messages[0]->getTitle();

        if($form->isSubmitted() && $form->isValid()){

            $message->setAuthor($user)
                    ->setConversation($conversation)
                    ->setCreatedAt(new \Datetime());
            ;
                              
            /*
            dans le cas d'une réponse de b à a à un message que a aurait déjà mis en corbeille, il faut
            lui sortir la conversation de la corbeille pour qu'il puisse récupérer son message.
            recuperation de corbeille de la target
            */          
            foreach ($message->getTarget() as $target) {

                if($conversation->isInCorbeille($target)){

                    $corbeilles=$conversation->getCorbeilles();

                    foreach ($corbeilles as $corbeille) {
                        if($corbeille->getConversation()==$conversation){
                            $conversation->removeCorbeille($corbeille);
                        }
                    }
                }
            }
            //-----------------------------------------------------------------------------

            $manager->persist($message);
            $manager->flush();

            //redirection sur show() pour maj de la page après soumission
            return $this->redirectToRoute('profil_conversations_show', array('id' => $conversation->getId()));
        }        

        return $this->render('profil/show.html.twig',[
            'messages'=>$messages, 
            'messageForm' => $form->createView(),
            'idLastAuthor' => $idAuthorLastMessage,
            'titleLastMessage'=>$titleLastMessage
        ]);
    }


    //fonction de suppression d'une conversation
    /**
     * @Route("/profil/messagerie/suppression/{id<\d+>}", name="profil_conversations_remove")
     */
    public function removeMessage(Conversation $conversation, Request $request, ObjectManager $manager){

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED'); 
        $user = $this->getUser();
       
        $corbeille=new Corbeille();
        $corbeille->setUser($user)
                ->setConversation($conversation)
                ->setCreatedAt(new \Datetime())
        ;

        $manager->persist($corbeille);
        $manager->flush();
   
        //suppression de la conversation de la bdd, si tous ces membres l'ont mise dans la corbeille     
        if($conversation->isReadyToDisappear()){
            $manager->remove($conversation); 
            $manager->flush();
            //supprime dans la bdd automatiquement toutes les entrées des différentes tables liées à cette conversation
        }

        return $this->redirectToRoute('profil_mail_reception');
    }

    //lien pour suppression du compte utilisateur :
    /**
     * @Route("/profil/user/{username}/goodbye", name="profil_remove")
     */
    public function removeUser(User $user, ObjectManager $manager){

        $userId=null;
        if($user==$this->getUser()){
            //nouvelle entrée dans la nouvell table 'user_bye'
            $userBye=new UserBye();
            $userBye->setUser($this->getUser());
            $userBye->setCreatedAt(new \Datetime());
            $manager->persist($userBye);
            $manager->flush();

            $urlLogout = $this->generateUrl('security_logout');       

            return $this->json([
                'code'=>200,
                'urlLogout'=> $urlLogout,
            ],200);             
        }  

       return $this->redirectToRoute('profil');
    }

    //page de profil public des users, page d'accueil, tab contribution topics et hors forum
    /**
     * @Route("/profil/user/{username}", name="profil_public")
     */
    public function userPublic(User $user, Request $request, PaginatorInterface $paginator, Assistant $assistant){


        $topics = $paginator->paginate(
            $assistant->orderByDateDesc($user->getTopics()), /* on classe les sujets du plus récent au plus vieux */
            $request->query->getInt('page', 1)/*page number*/,
            10/*limit per page*/
        );        

        return $this->render('profil/public.html.twig',[
            'user'=>$user,
            'topics'=>$topics,
        ]);
    }  

    //page de profil public des users, page d'accueil, tab contributin topics
    /**
     * @Route("/profil/user/{username}/commentaires", name="profil_public_comments")
     */
    public function userPublicReactions(User $user, TopicRepository $repo, Request $request, PaginatorInterface $paginator, Assistant $assistant){

        $topics=$repo->findAll();

        //on recupere les topics où l'user a commenté (qu'il soit l'auteur du topic ou non)
        $topicsWithUserReactions=[];
        foreach ($topics as $topic) {
             $check=false;
             foreach ($topic->getReactions() as $reaction) {
                if($reaction->getAuthor()==$user){
                    $check=true;
                }
            }
            if($check){
                $topicsWithUserReactions[]=$topic;
            }
        }

        //on supprime les reactions qui ne sont pas de l'user :     
        $topicsWithUserReactions=$this->removeReactionsNotByUser($topicsWithUserReactions, $user);
        $topicsWithUserReactions=$assistant->orderByDateDesc($topicsWithUserReactions);

        $topicsWithUserReactions = $paginator->paginate(
            $topicsWithUserReactions, /* on supprime les reactions qui ne sont pas de l'user */
            $request->query->getInt('page', 1)/*page number*/,
            10/*limit per page*/
        ); 

        return $this->render('profil/public-comments.html.twig',[
            'user'=>$user,
            'topics'=>$topicsWithUserReactions,            
        ]);
    }   

    //page de profil non public des users, pour l'administrateur, tab qui recence les commentaires de l'user qui ont été signalés
    /**
     * @Route("/profil/user/{username}/signalements", name="profil_signalements")
     */   
    public function userSignalements(User $user){

        $this->denyAccessUnlessGranted('ROLE_XXXXX');

        $reactions=$user->getReactions();
        $reactionsWithSignalement=[];
        foreach ($reactions as $reaction) {
            
            if($reaction->hasSignalements()){
                $reactionsWithSignalement[]=$reaction;
            }
        }

        //recupération du nombre de signalements total sur l'user
        $i=0;
        foreach ($reactionsWithSignalement as $reaction) {
           foreach ($reaction->getSignalements() as $signalement) {
               $i++;
           }
        }
    
        return $this->render('profil/signal.html.twig',[
            'user'=>$user,
            'reactions'=>$reactionsWithSignalement,
            'nSignalements'=>$i,
        ]);
    }   
        
    //page de profil non public des users, pour l'administrateur, tab qui recence les commentaires contents de l'user qui ont été signalés
    /**
     * @Route("/profil/user/{username}/signalements-contenus", name="profil_signalements_contents")
     */  
    public function userSignalementsContents(User $user, NewsRepository $repoNews, FilmRepository $repoFilm, MusicRepository $repoMusic){

        $this->denyAccessUnlessGranted('ROLE_XXXXX');

        $critics=$user->getCritics();
        $criticsWithSignalement=[];
        $slug='';
        foreach ($critics as $critic) {
            
            if($critic->hasSignalements()){
                //--recherche de l'item du critic
                $item='';
                $repo='';
                $route='';
                switch ($critic->getCategory()) {
                    case 'news':
                        $repo=$repoNews;
                        $slug="news";
                        $route='news_show';
                        break;
                    case 'film':
                        $repo=$repoFilm;
                        $slug="films";
                        $route='film_show';
                        break;
                    case 'music':
                        $repo=$repoMusic;
                        $slug="music";
                        $route='music_show';
                        break;                                        
                }
                $item=$repo->findOneBy(['id'=>$critic->getItemId()]);
                $critic->setItem($item);
                $critic->setRoute($route);
                //-------------------------
                $criticsWithSignalement[]=$critic;
            }
        }

        //recupération du nombre de signalements total sur l'user
        $i=0;
        foreach ($criticsWithSignalement as $critic) {
           foreach ($critic->getCriticSignalements() as $signalement) {
               $i++;
           }
        }
    
        return $this->render('profil/signal-contents.html.twig',[
            'user'=>$user,
            'slug'=>$slug,
            'route'=>'admin_comment_content_edit',
            'critics'=>$criticsWithSignalement,
            'nSignalements'=>$i,
        ]);
    } 

    //-------------
    //pour la fonction userPublicReactions(), qui affiche dans le profil de l'user son tableau de commentaires 
    //permet de supprimer les reactions qui ne sont pas de l'user selon une requête pour Paginator
    public function removeReactionsNotByUser($topicsWithUserReactions, $user){
        foreach ($topicsWithUserReactions as $topic) {
            foreach ($topic->getReactions() as $reaction) {
                if($reaction->getAuthor()!=$user){
                    $topic->removeReaction($reaction);
                }
            }
        }   
        return $topicsWithUserReactions;        
    }    

    //------------------
    //fonction commune à mailReception() et mailExpedition() ; range les messages du plus récent au vieux, et ne renvoie que les derniers messages de chaque conversation :
    public function getMails($messages,$user){

        $assistant=new Assistant();

        //On range les messages du plus récent au plus vieux
        $messagesOrdonnes=$assistant->orderByDateDesc($messages);

         //on ne garde que les derniers messages de chaque conversation (pour éviter les doublons de conversations, avec != messages qui appartiendraient à la même conversation)
        $conversationsId=[];
        $messagesUniqueConversation=[];

        foreach ($messagesOrdonnes as $message) {

            $inCorbeille=$message->getConversation()->isInCorbeille($user);

            $conversationId=$message->getConversation()->getId();
            if(!in_array($conversationId, $conversationsId) && $inCorbeille==false){
                 $messagesUniqueConversation[]=$message;
            }
            $conversationsId[]=$conversationId;           
        }

        return $messagesUniqueConversation;
    }   

    //--renvoie le nombre de messages lus par l'user; appelée avec en valeur dans paramètre le résultat de getMails() :
    public function getMailsRead($mails,$user){
        $mailsRead=0;
        foreach ($mails as $mail) {
            $views=$mail->getViews();
            foreach ($views as $view) {
                if($view->getViewer()==$user){
                    $mailsRead+=1;
                }
            }
        }
        return $mailsRead;        
    } 

}

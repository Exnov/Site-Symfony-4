<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;

use App\Repository\ForumRepository;
use App\Repository\ReactionLikeRepository;
use App\Entity\Forum;
use App\Entity\Topic;
use App\Form\TopicType;
use App\Entity\Reaction;
use App\Form\ReactionType;
use App\Entity\Signalement;
use App\Entity\ReactionLike;
use App\Form\SignalementType;

use  Knp\Component\Pager\PaginatorInterface;

use  App\Service\Assistant; //regroupe différentes fonctions partagées par les controllers


class ForumController extends AbstractController{

	//retourn page principale du forum
    /**
     * @Route("/forum", name="forum")
     */
    public function index(ForumRepository $repo){

    	$categories=$repo->findAll();     

        //données à préparer pour la composition du tableau dans template :
        //titres des rubriques, nbre messages et reactions par rubrique, et dernier message dans rubrique
        $forumInfos=[];

        foreach ($categories as $category) {
            $rubrique=[];
            $rubrique['category']=$category->getCategory();
            $rubrique['slug']=$category->getSlug();
            $rubrique['nbTopics']=count($category->getTopics());
            $rubrique['nbReactions']=$category->countReactions();
            $rubrique['lastReaction']=$category->getLastReaction();

            $forumInfos[]=$rubrique;
        }

        return $this->render('forum/index.html.twig', [
            'categories' => $forumInfos,
        ]);
    }


    //page de création d'un nouveau sujet de conversation
    /**
     * @Route("/forum/topic/create", name="forum_create")
     */
    public function createTopic(Request $request, ObjectManager $manager, Assistant $assistant){
        
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED'); 

        //------------------
        $topic=new Topic();
        //-------------------
        $reaction=new Reaction();
        //------------------
        $topic->getReactions()->add($reaction);
        //-------------------

        $form=$this->createForm(TopicType::class,$topic);
        $form->handleRequest($request);

        $user = $this->getUser();
        if($form->isSubmitted() && $form->isValid()){

            $reactionCopie=$reaction;
            //--           
            $titleUrl=$assistant->generateTitleForUrl($topic->getTitle());
            //--
            $topic->setAuthor($user)
                 ->setCreatedAt(new \Datetime())
                 ->removeReaction($reaction)
                 ->setTitleUrl($titleUrl)                 
            ;
            
            $manager->persist($topic);
            $manager->flush();

            $reactionCopie->setAuthor($user)
                ->setCreatedAt(new \Datetime())
                ->setTopic($topic)
            ;

            $manager->persist($reactionCopie);
            $manager->flush();
           
            //après soumission retour à la page du message affiché,cf show() avec forum/show.html.twig
            return $this->redirectToRoute('forum_topic',[
                'slug'=>$topic->getCategory()->getSlug(),
                'id'=>$topic->getId(),
                'titleUrl'=>$topic->getTitleUrl(),
            ]);          
        }     
        //-----------------

        return $this->render('forum/create.html.twig',[
            'topicForm'=>$form->createView(),
        ]);  
    }    

    //edition d'un commentaire
    /**
     * @Route("/forum/commentaire/{id<\d+>}/edit", name="forum_comment_edit")
     */
    public function editComment(Reaction $reaction, Request $request, ObjectManager $manager){ 

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED'); 

        //on verifie ensuite si l'user est bien l'auteur du reaction à éditer :
        $user = $this->getUser();
        $author = $reaction->getAuthor();        

        if($user==$author){

            $form=$this->createForm(ReactionType::class,$reaction);
            $form->handleRequest($request);

            $user = $this->getUser();
            if($form->isSubmitted() && $form->isValid()){
          
                $reaction->setAuthor($user)
                        ->setCreatedAt(new \Datetime())
                        ;

                $manager->persist($reaction);
                $manager->flush();

                $topic=$reaction->getTopic();

                //redirection vers le même topic
                return $this->redirectToRoute('forum_topic',[
                    'slug'=>$topic->getCategory()->getSlug(),
                    'id'=>$topic->getId(),
                    'titleUrl'=>$topic->getTitleUrl(),
                ]);           
            }

            return $this->render('forum/edit.html.twig',[
                'reactionForm'=>$form->createView(),
                'reaction'=>$reaction
            ]);
        }

        //sinon retour à l'accueil :
        else{
            return $this->redirectToRoute('home');
        }
    } 

   //retourne page de signalement d'un commentaire
    /**
     * @Route("/forum/commentaire/{id<\d+>}/signalement", name="forum_signal")
     */
    public function signal(Reaction $reaction, Request $request, ObjectManager $manager){

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED'); 

        //------------------
        $user = $this->getUser();

        //on verifie si l'user ne signale pas son propre commentaire :
        $author=$reaction->getAuthor();
        if($author==$user){
            return $this->redirectToRoute('home');
        }

        //on verifie ensuite si l'user n'a pas déjà signalé le commentaire :       
        $signals=$reaction->getSignalements();
        $alreadySignaled=false;
        foreach ($signals as $signal) {
            if($user==$signal->getUser()){
                return $this->redirectToRoute('home');
            }
        }
        //------------------
       
        $signalement=new Signalement();
        $form=$this->createForm(SignalementType::class,$signalement);

        $form->handleRequest($request);
        $user = $this->getUser();


        if($form->isSubmitted() && $form->isValid()){
      
            $signalement->setUser($user)
                    ->setCreatedAt(new \Datetime())
                    ->setReaction($reaction)
                    ;

            $manager->persist($signalement);
            $manager->flush();

            //redirection vers le même topic :
            $idTopic=$reaction->getTopic()->getId();
            $slugTopic=$reaction->getTopic()->getCategory()->getSlug();     

            return $this->redirectToRoute('forum_topic',[
                'slug'=>$slugTopic,
                'id'=>$idTopic,
                'titleUrl'=>$reaction->getTopic()->getTitleUrl(),
            ]);
        }
        

        return $this->render('forum/signal.html.twig',[
                'signalementForm'=>$form->createView(),
                'reaction'=>$reaction,
        ]);

    }          

    //suppression d'un topic
    /**
     * @Route("/forum/topic/{id<\d+>}/suppression", name="forum_topic_remove")
     */
    public function removeTopic(Topic $topic, ObjectManager $manager){

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED'); 

        //on verifie ensuite si l'user est bien l'auteur du topic à supprimer :
        $user = $this->getUser();
        $author = $topic->getAuthor();        

        if($user==$author){
     
            $slug=$topic->getCategory()->getSlug();
            $manager->remove($topic);
            $manager->flush();

            //redirection vers le tableau des catégories du domaine du sujet (cinéma, politique, ...)     
            return $this->redirectToRoute('profil_topics'); 
        }

        //sinon retour à l'accueil :
        else{
            return $this->redirectToRoute('home');
        }
    }

    //suppression d'un commentaire
    /**
     * @Route("/forum/commentaire/{id<\d+>}/suppression", name="forum_comment_remove")
     */
    public function removeReaction(Reaction $reaction, ObjectManager $manager){

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED'); 

        //on verifie ensuite si l'user est bien l'auteur du reaction à supprimer :
        $user = $this->getUser();
        $author = $reaction->getAuthor();        

        if($user==$author){        

            $topic=$reaction->getTopic();

            $manager->remove($reaction);
            $manager->flush();
                
            //si le topic ne contient plus de reactions on le supprime
            if($topic->hasReactions()==false){

                $slug=$topic->getCategory()->getSlug();
                $manager->remove($topic);
                $manager->flush();   
                //redirection vers le tableau des catégories du domaine du sujet (cinéma, politique, ...)
                return $this->redirectToRoute('profil_comments');        
            }

            return $this->redirectToRoute('forum_topic',[
                    'slug'=>$topic->getCategory()->getSlug(),
                    'id'=>$topic->getId(),
                    'titleUrl'=>$topic->getTitleUrl(),
            ]);  
        }   

        //sinon retour à l'accueil :
        else{
            return $this->redirectToRoute('home');
        }        
    }

    //-------------------------------------------------------------------------------------------------------------
    /**
     * Permet de liker ou unliker un article
     *
     * @Route("forum/commentaire/{id<\d+>}/like", name="reaction_like")
     *
     */
    public function like(Reaction $reaction, ObjectManager $manager, ReactionLikeRepository $likeRepo){

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED'); 

        $user=$this->getUser();

        #si user connecté
        #si le like existe déjà, suppression du like
        if($reaction->isLikedByUser($user)){
        #retrouver le like par rapport à l'article et l'user courant             
            $like=$likeRepo->findOneBy([
                'reaction'=>$reaction,
                'user'=>$user
            ]);

            $manager->remove($like);
            $manager->flush();

            #maj json pour affichage
            return $this->json([
                'code'=>200,
                'message'=>'Like bien supprimé',
                'likes'=>$likeRepo->count(['reaction'=>$reaction])
            ],200);
        }

        #sinon, nouveau like, on l'ajoute alors
        $like=new ReactionLike();
        $like->setReaction($reaction)
            ->setUser($user)
        ;

        $manager->persist($like);
        $manager->flush();

        return $this->json([
            'code'=>200,
            'message'=>'Like bien ajouté',
            'likes'=>$likeRepo->count(['reaction'=>$reaction])
        ],200);
    }
    //-------------------------------------------------------------------------------------------------------------
      
    //retourne page du message
    //gère aussi le forumulaire de commentaires du topic
    /**
     * @Route("/forum/{slug}/{id<\d+>}/{titleUrl}", name="forum_topic")
     */
    public function show(Topic $topic, Request $request, ObjectManager $manager, PaginatorInterface $paginator, Assistant $assistant){

        $reactions = $paginator->paginate(
            $topic->getReactions(),
            $request->query->getInt('page', 1)/*page number*/,
            10/*limit per page*/
        );
        //------------------
        $reaction=new Reaction();
        $form=$this->createForm(ReactionType::class,$reaction);

        $form->handleRequest($request);

        $user = $this->getUser();
        if($form->isSubmitted() && $form->isValid()){
      
            $reaction->setAuthor($user)
                    ->setCreatedAt(new \Datetime())
                    ->setTopic($topic)
                    ;

            $manager->persist($reaction);
            $manager->flush();

            //redirection vers le même topic
            return $this->redirectToRoute('forum_topic',[
                'slug'=>$topic->getCategory()->getSlug(),
                'id'=>$topic->getId(),
                'titleUrl'=>$topic->getTitleUrl(),
            ]);
        }       
        //------------------

    	return $this->render('forum/show.html.twig',[
    		'topic'=>$topic,
            'reactions'=>$reactions,
            'reactionForm'=>$form->createView(),
    	]);
    }
   

    //retourne page rubriques du forum
    /**
     * @Route("/forum/{slug}", name="forum_category")
     */
    public function category(Forum $forum, Request $request, PaginatorInterface $paginator, Assistant $assistant){

         $topics = $paginator->paginate(
            $assistant->orderByDateDesc($forum->getTopics()), /* on classe les sujets du plus récent au plus vieux */
            $request->query->getInt('page', 1)/*page number*/,
            10/*limit per page*/
        );        

    	return $this->render('forum/category.html.twig',[
    		'forum'=>$forum,
            'topics'=>$topics
    	]);
    }
    
}

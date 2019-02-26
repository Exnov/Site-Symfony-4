<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;

use App\Repository\UserRepository;
use App\Repository\SignalementRepository;
use App\Repository\TopicRepository;
use App\Repository\ReactionRepository;
use App\Repository\ForumRepository;
use App\Repository\NewsRepository;
use App\Repository\FilmRepository;
use App\Repository\MusicRepository;
use App\Repository\CriticRepository;
use App\Repository\ContactRepository;
use App\Repository\SocialNetworkRepository;
use App\Repository\HomepageRepository;
use App\Repository\BackgroundRepository;
use App\Repository\MasterBackgroundRepository;
use App\Repository\logosRepository;
use App\Repository\MasterLogosRepository;
use App\Repository\SeoRepository;

use App\Form\ReactionType;
use App\Form\ForumType;
use App\Form\CriticType;
use App\Form\SocialNetworkType;
use App\Form\HomepageType;
use App\Form\BackgroundType;
use App\Form\MasterBackgroundType;
use App\Form\logosType;
use App\Form\MasterLogosType;

use App\Entity\Reaction;
use App\Entity\Topic;
use App\Entity\User;
use App\Entity\Forum;
use App\Entity\Critic;
use App\Entity\Contact;
use App\Entity\SocialNetwork;
use App\Entity\Homepage;
use App\Entity\Background;
use App\Entity\MasterBackground;
use App\Entity\Logos;
use App\Entity\MasterLogos;
use App\Entity\Seo;

use Symfony\Component\HttpFoundation\File\Exception\FileException; //image
use App\Service\FileUploader; //image
use  Knp\Component\Pager\PaginatorInterface;

use  App\Service\Assistant; //regroupe différentes fonctions partagées par les controllers
use  App\Service\Stats; //regroupe fonctions stats

use Symfony\Component\Form\Extension\Core\Type\TextType;


class AdminController extends AbstractController{


    /**
     * @Route("/admin", name="admin")
     */
    public function index(UserRepository $repo, Request $request, PaginatorInterface $paginator, Assistant $assistant)
    {

        $users=$repo->findAll(); 
        $sectionSelect="/admin/section-users.html.twig";

        $users = $paginator->paginate(
            $assistant->orderByDateDesc($users),
            $request->query->getInt('page', 1)/*page number*/,
            10/*limit per page*/
        );

        return $this->render('admin/index.html.twig', [
            'users'=>$users,
            'sectionSelect'=>$sectionSelect,
            'refPaginator'=>$users, 
            'rubrique'=>'Utilisateurs',
        ]);
    }

    //admin du logo et du favicon : pour la gestion du form on se base sur showBackground(), et non showSocial()
    /**
     * @Route("/admin/logos", name="admin_logos")
     */
    public function showLogos(MasterLogosRepository $repo, Request $request, ObjectManager $manager, FileUploader $fileUploader){

        $masterLogos = new MasterLogos();
        $logo=new Logos();
        $response=$this->managerShow ($repo,$masterLogos,$logo,'logos',$fileUploader,MasterLogosType::class,$request,$manager);

        return $response;
    }   

    //pour supprimer les images dans logos
    /**
     * @Route("/admin/logos/{name<\d+>}/remove", name="admin_logo_remove")
     */
    public function removeLogo(MasterLogosRepository $repo, Request $request, ObjectManager $manager){

        $response=$this->managerRemove($repo,'logos', $request, $manager);
        return $response;
    }


    /**
     * @Route("/admin/background", name="admin_background")
     */
    public function showBackground(MasterBackgroundRepository $repo, Request $request, ObjectManager $manager, FileUploader $fileUploader){

        //l'entité MasterBackground et sa table nous servent d'intermédiaire pour enregistrer nos backgrounds
        //cf https://symfony.com/doc/current/form/form_collections.html

        $masterBg = new MasterBackground();
        $bg=new Background();
        $response=$this->managerShow ($repo,$masterBg,$bg,'backgrounds',$fileUploader,MasterBackgroundType::class,$request,$manager);

        return $response;
    }

    //pour supprimer les images dans background
    /**
     * @Route("/admin/background/{name<\d+>}/remove", name="admin_background_remove")
     */
    public function removeBackground(MasterBackgroundRepository $repo, Request $request, ObjectManager $manager){

        $response=$this->managerRemove($repo,'backgrounds', $request, $manager);
        return $response;
    }

    /**
     * @Route("/admin/homepage", name="admin_homepage")
     */
    public function showHomepage(HomepageRepository $repo, Request $request, ObjectManager $manager, FileUploader $fileUploader, Assistant $assistant){

        $homepageDb=$repo->findAll();
        $urlBgNews='';
        $urlBgCommunity='';
        $urlPoster='';
        $urlVideo='';
        $urlLogo='';

        $homepage=new Homepage();
        if(count($homepageDb)>0){
            $homepage=$homepageDb[0]; //1 seule ligne dans table de Homepage
            $urlBgNews=$assistant->checkExtension($homepage->getBgNews());
            $urlBgCommunity=$assistant->checkExtension($homepage->getBgCommunity());
            $urlPoster=$assistant->checkExtension($homepage->getPoster());
            $urlVideo=$assistant->checkExtension($homepage->getVideo());
            $urlLogo=$assistant->checkExtension($homepage->getLogo());
        } 

        $form=$this->createForm(HomepageType::class,$homepage);
        $form->handleRequest($request);
        //--

        $actions=[
            'bgNews'=>'setBgNews',
            'bgCommunity'=>'setBgCommunity',
            'poster'=>'setPoster',
            'video'=>'setVideo',    
            'logo'=>'setLogo',                                 
        ];           

        if($form->isSubmitted() && $form->isValid()){

            //gestion des images------------------------------------------------------
            //boucle :       
            foreach ($request->files->get('homepage') as $key => $value) {

                $func=$actions[$key];

                //si chargement de nouvelle image, on prend en compte la nouvelle image chargée
                if($request->files->get('homepage')[$key]!=null){

                    $file =$request->files->get('homepage')[$key];
                    $fileName = $fileUploader->uploadImage($file,'homepage');                
                    $homepage->$func($fileName);               
                }       
            }

            $homepage->setCreatedAt(new \Datetime())
            ;

            $manager->persist($homepage);
            $manager->flush();

            return $this->redirectToRoute('admin_homepage'); 
        }        

        $sectionSelect="/admin/section-homepage.html.twig";

        $urls=[
            'bgNews'=>$urlBgNews,
            'bgCommunity'=>$urlBgCommunity,
            'poster'=>$urlPoster,
            'video'=>$urlVideo, 
            'logo'=>$urlLogo,                                    
        ];        

        return $this->render('admin/index.html.twig', [
            'sectionSelect'=>$sectionSelect,
            'rubrique'=>'Homepage',
            'form'=>$form->createView(),      
            'urls'=>$urls,                             
        ]);
    }

    //pour supprimer les images dans homepage
    /**
     * @Route("/admin/homepage/{key}/remove", name="admin_homepage_remove", requirements={"key"="bgNews|bgCommunity|poster|video|logo"})
     */
    public function removeHomepageMedias(HomepageRepository $repo, Request $request, ObjectManager $manager){

                $homepage=$repo->findAll()[0]; //si suppression c'est que la ligne existe déjà

                $key=$request->attributes->get('key');

                $func='set'.$key;
                $homepage->$func(null); 
              
                $manager->persist($homepage);
                $manager->flush();               

                return $this->json([
                    'code'=>200,
                    'key'=>$key,
                ],200);
    } 

    //SEO : calqué sur showSocial(), et saveSocial()
    /**
     * @Route("/admin/seo", name="admin_seo")
     */
    public function showSeo(SeoRepository $repo){

        $seo=$repo->findAll(); 
        $sectionSelect="/admin/section-seo.html.twig";

        return $this->render('admin/index.html.twig', [
            'seo'=>$seo,
            'sectionSelect'=>$sectionSelect,
            'rubrique'=>'SEO',
        ]);
    }    

    /**
     * @Route("/admin/seo/save", name="admin_seo_save")
     */
    public function saveSeo(SeoRepository $repo, Request $request, ObjectManager $manager){

        //recupération des données :
        foreach ($request->request as $key => $value) {
            
            //recupère l'id : fin de $key
            $id=substr ($key,-1);
            $line=$repo->findOneBy(['id'=>$id]);

            $line->setDescription($value);

            $manager->persist($line);
            $manager->flush();                     
        }

        return $this->json([
            'code'=>200,
        ],200);
    }    

    /**
     * @Route("/admin/social", name="admin_social")
     */
    public function showSocial(SocialNetworkRepository $repo){

        $reseaux=$repo->findAll(); 
        $sectionSelect="/admin/section-social.html.twig";

        return $this->render('admin/index.html.twig', [
            'reseaux'=>$reseaux,
            'sectionSelect'=>$sectionSelect,
            'rubrique'=>'Réseaux sociaux',
        ]);
    } 

    /**
     * @Route("/admin/social/save", name="admin_social_save")
     */
    public function saveSocial(SocialNetworkRepository $repo, Request $request, ObjectManager $manager){

        //recupération des données :
        foreach ($request->request as $key => $value) {
            
            //recupère l'id : fin de $key
            $id=substr ($key,-1);
            $reseau=$repo->findOneBy(['id'=>$id]);

            //recupère l'indice sur le champ à affecter : debut de $key
            $indice=substr ($key,0,-2);
            switch ( $indice) {
                case 'url':
                    $reseau->setUrl($value);
                    break;
                 case 'logo':
                    $reseau->setFontawesome($value);
                    break;                   
            }

            $manager->persist($reseau);
            $manager->flush();          
        }

        return $this->json([
            'code'=>200,
        ],200);
    }          

    /**
     * @Route("/admin/contact", name="admin_contact")
     */
    public function showContact(ContactRepository $repo, Request $request, PaginatorInterface $paginator, Assistant $assistant)
    {

        $messages=$repo->findAll(); 
        $sectionSelect="/admin/section-contact.html.twig";

        $messages = $paginator->paginate(
            $assistant->orderByDateDesc($messages),
            $request->query->getInt('page', 1)/*page number*/,
            10/*limit per page*/
        );

        return $this->render('admin/index.html.twig', [
            'messages'=>$messages,
            'sectionSelect'=>$sectionSelect,
            'refPaginator'=>$messages, 
            'rubrique'=>'Messages de contact',
        ]);
    }    

    /**
     * @Route("/admin/forum/topics", name="admin_topics")
     */
    public function showTopics(TopicRepository $repo, Request $request, PaginatorInterface $paginator, Assistant $assistant)
    {

        $topicsRecup=$repo->findAll();
        $topicsRecup=$assistant->orderByDateDesc($topicsRecup);

        $sectionSelect="/admin/forum/section-topics.html.twig";

        $topics = $paginator->paginate(
            $topicsRecup,
            $request->query->getInt('page', 1)/*page number*/,
            10/*limit per page*/
        );         

        return $this->render('admin/index.html.twig', [
            'topics'=>$topics,
            'sectionSelect'=>$sectionSelect,
            'refPaginator'=>$topics,
            'rubrique'=>'Topics',
        ]);
    }    

    //page pour forum, tab avec gestion des categories
    /**
     * @Route("/admin/forum/categories", name="admin_categories")
     */
    public function showCategories(ForumRepository $repo, Request $request, ObjectManager $manager, PaginatorInterface $paginator, Assistant $assistant){

        //formulaire
        $forum =new Forum();
        $form=$this->createForm(ForumType::class,$forum);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            //slug et date
            $category=$forum->getCategory();
            $slug=$forum->createSlug($category);
            $forum->setSlug($slug)
                  ->setCreatedAt(new \Datetime())
            ;

            $manager->persist($forum);
            $manager->flush();
        }        

        //----------------
        $categoriesRecup=$repo->findAll();
        $categoriesRecup=$assistant->orderByDateDesc($categoriesRecup);   

        $sectionSelect="/admin/forum/section-categories.html.twig";    

        $categories = $paginator->paginate(
            $categoriesRecup,
            $request->query->getInt('page', 1)/*page number*/,
            10/*limit per page*/
        );         

        return $this->render('admin/index.html.twig', [
            'categories'=>$categories,
            'categoryForm'=>$form->createView(),
            'sectionSelect'=>$sectionSelect,
            'refPaginator'=>$categories,
            'rubrique'=>'Catégories',
        ]);
    }


    //page pour forum, tab avec gestion des signalements
    /**
     * @Route("/admin/forum/signalements", name="admin_signals")
     */
    public function showSignalsForum(ReactionRepository $repo, Request $request, PaginatorInterface $paginator)
    {

        $reactions=$repo->findAll();

        $reactionsRecup=$this->getReactionsWithSignalement($reactions);
        $reactionsRecup=$this->orderReactionBySignalDesc($reactionsRecup);

        $sectionSelect="/admin/forum/section-signals.html.twig";   

        $reactionsWithSignalements = $paginator->paginate(
            $reactionsRecup, 
            $request->query->getInt('page', 1)/*page number*/,
            10/*limit per page*/
        );          

        return $this->render('admin/index.html.twig', [
            'reactions'=>$reactionsWithSignalements,
            'sectionSelect'=>$sectionSelect,
            'refPaginator'=>$reactionsWithSignalements,
            'rubrique'=>'Commentaires signalés',
        ]);        
    } 

    //--pour la fonction showSignalsForum()
    //-- on recupére uniquement les reactions avec un ou des signalements selon une requête à fournir à Paginator
    public function getReactionsWithSignalement($reactions){
        $tab=[];
         foreach ($reactions as $reaction) {
            if($reaction->hasSignalements()){
                $tab[]=$reaction;
            }
        } 
        return $tab;          
    }

    public function orderReactionBySignalDesc($array){
        //on recupère les signals ordonnés pour avoir une liste de reférences ordonnées de la  plus récente à la plus ancienne
        $signals=[];
        foreach ($array as $elt) {
            if(!in_array(count($elt->getSignalements()), $signals)){ //on ne remet pas de signal qui serait déjà recupéré
                $signals[]=count($elt->getSignalements());
            }          
        }
        rsort($signals);

        $x=[];
        foreach ($signals as $signal) {          
            foreach ($array as $elt) {
                if(count($elt->getSignalements())==$signal){
                    $x[]=$elt;
                }
            }
        }
    
        return $x;
    }  

    //pour voir les messages envoyés dans Contact
    /**
     * @Route("/admin/contact/{id<\d+>}", name="admin_contact_msg")
     */
    public function showContactMsg(Contact $contact){
    
        return $this->render('admin/show-contact-msg.html.twig', [
            'contact'=>$contact,
        ]);
    } 

    //pour supprimer un message de contact
    /**
     * @Route("/admin/contact/{id<\d+>}/suppression", name="admin_contact_msg_remove")
     */
     public function removeContactMsg(Contact $contact, ObjectManager $manager){

        $manager->remove($contact);
        $manager->flush();

        return $this->redirectToRoute('admin_contact'); 
    }               

    //pour voir les signalements avec le reaction signalé
    /**
     * @Route("/admin/forum/commentaire/{id<\d+>}", name="admin_signal_show")
     */
    public function showSignalForum(Reaction $reaction){
   
        return $this->render('admin/forum/show-signal.html.twig', [
            'reaction'=>$reaction,
        ]);
    }   

    //pour supprimer un reaction signalé; comme dans ForumController avec redirection différente
    /**
     * @Route("/admin/forum/commentaire/{id<\d+>}/suppression", name="admin_comment_remove")
     */
     public function removeCommentForum(Reaction $reaction, ObjectManager $manager){

        $topic=$reaction->getTopic();

        $manager->remove($reaction);
        $manager->flush();
      
        //si le topic ne contient plus de reactions on le supprime
        if($topic->hasReactions()==false){

            $slug=$topic->getCategory()->getSlug();
            $manager->remove($topic);
            $manager->flush();   
            //redirection vers le tableau des catégories du domaine du sujet (cinéma, politique, ...)
            return $this->redirectToRoute('admin_topic');         
        }

        return $this->redirectToRoute('admin_topic_edit',[
            'id'=>$topic->getId(),
        ]);
    }

    //page d'édition d'un reaction signalé; cf forum_comment_edit() de ForumController
    /**
     * @Route("/admin/forum/commentaire/{id<\d+>}/edit", name="admin_comment_edit")
     */
    public function editCommentForum(Reaction $reaction, Request $request, ObjectManager $manager,UserRepository $repo){ 

        $form=$this->createForm(ReactionType::class,$reaction);
        $form->handleRequest($request);

        $user = $reaction->getAuthor();
        $user=$repo->findOneBy(['id'=>$user->getId()]); //on recupere l'user à partir de son id

        if($form->isSubmitted() && $form->isValid()){
      
            //modification du reaction
            $reaction->setAuthor($user);

            //suppression du ou des signalement(s)
            foreach ($reaction->getSignalements() as $signalement) {
                $reaction->removeSignalement($signalement);
            }

            $manager->persist($reaction);
            $manager->flush();                       
        }

        return $this->render('admin/forum/edit-signal.html.twig',[
            'reaction'=>$reaction,
            'reactionForm'=>$form->createView(),
        ]);
    }  

    /**
     * @Route("/admin/forum/topic/{id<\d+>}/edit", name="admin_topic_edit")
     */
    public function editTopic(Topic $topic){ 
    
            return $this->render('admin/forum/show-topic.html.twig',[
                'topic'=>$topic,
            ]);
    }  


    //pour supprimer un topic
    /**
     * @Route("/admin/forum/topic/{id<\d+>}/suppression", name="admin_topic_remove")
     */
     public function removeTopic(Topic $topic, ObjectManager $manager){

        $manager->remove($topic);
        $manager->flush();

        return $this->redirectToRoute('admin_topics'); 
    }  

    //pour supprimer une catégorie
    /**
     * @Route("/admin/forum/categorie/{id<\d+>}/suppression", name="admin_category_remove")
     */
    public function removeCategory(Forum $forum, ObjectManager $manager){
        
        $manager->remove($forum);
        $manager->flush();       
     
        return $this->redirectToRoute('admin_categories');
    } 

    //-- News/Films/Music
    /**
     * @Route("/admin/{slug}/signalements", name="admin_allsignals", requirements={"slug"="news|films|music"})
     */
    public function showSignalsContent(CriticRepository $repo, FilmRepository $repoFilm, NewsRepository $repoNews, MusicRepository $repoMusic, Request $request, PaginatorInterface $paginator)
    {

        //recuperation des routes en fonction de la catégorie d'appartenance du critic (news, film, music)
        $slug=$request->attributes->get('slug');
        $routeItem='';
        $repoItems='';
        $rubrique=' : commentaires signalés';
        switch ($slug) {
            case 'news':
                $category='news';
                $routeItem='news_show';
                $repoItems=$repoNews;
                $rubrique='News'.$rubrique;
                break;
            case 'films':
                $category='film';  
                $routeItem='film_show';
                $repoItems=$repoFilm;
                $rubrique='Films'.$rubrique;
                 break;       
            case 'music':
                $category='music';
                $routeItem='music_show';
                $repoItems=$repoMusic;
                $rubrique='Albums'.$rubrique;
                 break;          
        }

        $critics=$repo->findBy([
            'category' => $category,
        ]);      

        $criticsRecup=$this->getCriticsWithSignalement($critics, $repoItems);
        $criticsRecup=$this->orderCriticBySignalDesc($criticsRecup);

        $sectionSelect="/admin/content/section-signals.html.twig";   

        $criticsWithSignalements = $paginator->paginate(
            $criticsRecup,
            $request->query->getInt('page', 1)/*page number*/,
            10/*limit per page*/
        );          

        return $this->render('admin/index.html.twig', [
            'critics'=>$criticsWithSignalements,
            'sectionSelect'=>$sectionSelect,
            'refPaginator'=>$criticsWithSignalements,
            'rubrique'=>$rubrique,
            'routeItem'=>$routeItem,
            'slug'=>$slug,
        ]);        
    } 

    //-- on recupére uniquement les reactions avec un ou des signalements selon une requête à fournir à Paginator
    public function getCriticsWithSignalement($critics, $repoItems){
        $tab=[];
         foreach ($critics as $critic) {
            if($critic->hasSignalements()){
                //------
                 $item=$repoItems->findOneBy([
                    'id'=> $critic->getItemId(),
                ]);               
                $critic->setItem($item);
                //------
                $tab[]=$critic;
            }
        } 
        return $tab;          
    }        


    public function orderCriticBySignalDesc($array){

        //on recupère les signals ordonnés pour avoir une liste de reférences ordonnées de la plus récente à la plus ancienne
        $signals=[];
        foreach ($array as $elt) {
            if(!in_array(count($elt->getCriticSignalements()), $signals)){ //on ne remet pas de signal qui serait déjà recupérée
                $signals[]=count($elt->getCriticSignalements());
            }           
        }
        rsort($signals);

        $x=[];
        foreach ($signals as $signal) {
            
            foreach ($array as $elt) {
                if(count($elt->getCriticSignalements())==$signal){
                    $x[]=$elt;
                }
            }
        }
    
        return $x;
    }


    //pour voir les signalements avec le critic signalé (news, films, music)
    /**
     * @Route("/admin/{slug}/commentaire/{id<\d+>}", name="admin_signal_show_content", requirements={"slug"="news|films|music"})
     */
    public function showSignalContent(Critic $critic, FilmRepository $repoFilm, NewsRepository $repoNews, MusicRepository $repoMusic, Request $request){

        //recuperation des routes en fonction de la catégorie d'appartenance du critic (news, film, music)
        $data=$this->getDataItemForCritic($critic, $repoFilm, $repoNews, $repoMusic, $request);
        $slug=$data['slug'];
        $routeItem=$data['routeItem'];
        $rubrique=$data['rubrique'];
        $critic->setItem($data['item']);       
        //------------    
        
        return $this->render('admin/content/show-signal.html.twig', [
            'critic'=>$critic,
            'routeItem'=>$routeItem,
            'rubrique'=>$rubrique,
            'slug'=>$slug,
        ]);
    }     

    //page d'édition d'un reaction signale; cf forum_comment_edit() de ForumController
    /**
     * @Route("/admin/{slug}/commentaire/{id<\d+>}/edit", name="admin_comment_content_edit", requirements={"slug"="news|films|music"})
     */
    public function editCommentContent(Critic $critic, FilmRepository $repoFilm, NewsRepository $repoNews, MusicRepository $repoMusic, Request $request, ObjectManager $manager){ 

        //recuperation des routes en fonction de la catégorie d'appartenance du critic (news, film, music)
        $data=$this->getDataItemForCritic($critic, $repoFilm, $repoNews, $repoMusic, $request);
        $slug=$data['slug'];
        $routeItem=$data['routeItem'];
        $rubrique=$data['rubrique'];
        $critic->setItem($data['item']);       
        //------------

        $form=$this->createForm(CriticType::class, $critic);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
      
            //modification du reaction
            //suppression du ou des signalement(s)
            foreach ($critic->getCriticSignalements() as $signalement) {
                $critic->removeCriticSignalement($signalement);
            }

            $manager->persist($critic);
            $manager->flush();                        
        }

        return $this->render('admin/content/edit-signal.html.twig',[
            'critic'=>$critic,
            'criticForm'=>$form->createView(),
            'routeItem'=>$routeItem,
            'slug'=>$slug,           
        ]);
    }      

    //recupère pour showSignalContent() et editCritic() l'item du commentaire signalé
    public function getDataItemForCritic(Critic $critic, FilmRepository $repoFilm, NewsRepository $repoNews, MusicRepository $repoMusic, Request $request){

        //recuperation des routes en fonction de la catégorie d'appartenance du critic (news, film, music)
        $slug=$request->attributes->get('slug');

        $tab=[
            'slug' =>$slug,
            'category'=>'',
            'routeItem'=>'',
            'rubrique'=>'',
            'item'=>'',
        ];

        switch ($slug) {
            case 'news':
                $tab['category']='news';
                $tab['routeItem']='news_show';
                $tab['rubrique']='News';
                $repoItem=$repoNews;
                break;
            case 'films':
                $tab['category']='film';  
                $tab['routeItem']='film_show';
                $tab['rubrique']='Films';
                $repoItem=$repoFilm;
                 break;       
            case 'music':
                $tab['category']='music';
                $tab['routeItem']='music_show';
                $tab['rubrique']='Albums';
                $repoItem=$repoMusic;
                break;          
        }

        $tab['item']=$repoItem->findOneBy([
            'id'=> $critic->getItemId(),
        ]); 

        return $tab;
    }

    //pour supprimer un reaction signalé; comme dans ForumController avec redirection différente
    /**
     * @Route("/admin/{slug}/commentaire/{id<\d+>}/suppression", name="admin_comment_content_remove", requirements={"slug"="news|films|music"})
     */
    public function removeCommentContent(Critic $critic, ObjectManager $manager){

        $category=$critic->getCategory();
        $lug='';
        switch ($category) {
            case 'news':
                $slug='news';
                break;
            case 'film': 
                $slug='films';
                 break;       
            case 'music': 
                $slug='music';
                 break;          
        }        

        $manager->remove($critic);
        $manager->flush();
      
        return $this->redirectToRoute('admin_allsignals',[
            'slug'=>$slug,
        ]);
    }    
             
    //--------------------------------------------------------------------------------------
    //stats:
    /**
     * @Route("/admin/statistiques", name="admin_stats")
     */
    public function showStats(UserRepository $repoUser, TopicRepository $repoTopic, ReactionRepository $repoReaction, ForumRepository $repoForum, Stats $stats){

        $typeUser='%"ROLE_XXXXX"%'; 
        //infos générales pour tableau 
        $nUsers=$stats->getNumberOfData($repoUser->countAllExceptAdmin($typeUser));
        $nCategories=$stats->getNumberOfData($repoForum->countAll());
        $nTopics=$stats->getNumberOfData($repoTopic->countAllExceptAdmin($typeUser));
        $nReactions=$stats->getNumberOfData($repoReaction->countAllExceptAdmin($typeUser));
        $nLikes=$stats->getNumberOfData($repoReaction->countAllLikesExceptAdmin($typeUser));
        $nSignals=$stats->getNumberOfData($repoReaction->countAllSignalsExceptAdmin($typeUser));

        return $this->render('admin/stats.html.twig',[
            'nUsers'=>$nUsers,
            'nCategories'=>$nCategories,
            'nTopics'=>$nTopics,
            'nReactions'=>$nReactions,
            'nLikes'=>$nLikes,
            'nSignals'=>$nSignals,
            'rubrique'=>'Statistiques',
        ]);
    }
  
    //graph stats:
    /**
     * @Route("/admin/statistiques/graph", name="admin_stats_graph")
     */
    public function graph(UserRepository $repoUser, TopicRepository $repoTopic, ReactionRepository $repoReaction, ForumRepository $repoForum, Stats $stats){

        //recupération de l'année transmise par ajax qui la recupère dans l'input de l'année :
        $year=$_GET['year']; //on recupère l'année de départ
        $x=intval($year);
        $yearFin=strval($x+=1); //on construit l'année de fin en incrémentant d'1 l'année de départ
        $complementYear='-01-01';
        $year.=$complementYear;
        $yearFin.=$complementYear;

        $debut = new \DateTime($year); 
        $fin = new \DateTime($yearFin); 
        $typeUser='%"ROLE_XXXXX"%'; 

        //---------------------------
        //nombre de nouveaux users créés par mois
        $users=$repoUser->findUsersByYear($debut,$fin,$typeUser);
        $tabUsers=$stats->getNbContentGraph($users);

        //------------
        //nombre de nouveaux topics créés par mois
        $topics=$repoTopic->findTopicsByYear($debut,$fin,$typeUser);
        $tabTopics=$stats->getNbContentGraph($topics);

        $tabAuthorsTopics=$stats->getNbCreatorGraph($topics);     
        //----------------
        //nombre de nouveaux reactions créés par mois
        $reactions=$repoReaction->findReactionsByYear($debut,$fin,$typeUser);
        $tabReactions=$stats->getNbContentGraph($reactions);
        //---------------
        $tabAuthorsReactions=$stats->getNbCreatorGraph($reactions);

        //Taux de participation des créateurs dans le forum (topics, messages) par mois
        $usersReverse=$repoUser->findUsersByYearReverse($debut,$fin,$typeUser); //tous les users avant 2019
        
        $topicsImplication=$stats->getTauxImplicationGraph($usersReverse,$tabUsers,$tabAuthorsTopics);
        $reactionsImplication=$stats->getTauxImplicationGraph($usersReverse,$tabUsers,$tabAuthorsReactions);

        //---------------
        //recupération des noms de catégorie
        $categories=$repoForum->findCategoriesByYear($fin);    
        //nombre de nouveaux topics créés par mois et par catégories pour une année 
        $topicsByCategory=$stats->getNbContentGraphByCategory($topics,$categories,'topic');

        //nombre de nouveaux reactions créés par mois et par catégories pour une année
        $reactionsByCategory=$stats->getNbContentGraphByCategory($reactions,$categories,'reaction');
        //---------------

        //--------------
        //liste des producteurs de topics sur une année, sous forme de camembert
        $topicsCreatorsList=$repoTopic->findTopAuthorsInTopics($debut,$fin,$typeUser);
        $topicsCreatorsList=$stats->makePercentChart($topics,$topicsCreatorsList);
        

        //liste des producteurs de topics sur une année, sous forme de camembert
        $reactionsCreatorsList=$repoReaction->findTopAuthorsInReactions($debut,$fin,$typeUser);
        $reactionsCreatorsList=$stats->makePercentChart($reactions,$reactionsCreatorsList);

        //users likés
        $usersLiked=$repoUser->findUsersLiked($debut,$fin,$typeUser);
        $usersLiked=$stats->makeNumericChart($usersLiked);

        //users signaleurs
        $usersSignaleurs=$repoUser->findUsersSignaleurs($debut,$fin,$typeUser);
        $usersSignaleurs=$stats->makeNumericChart($usersSignaleurs);       
        
        //users signalés :
        $usersSignaled=$repoReaction->findUsersSignaled($debut,$fin,$typeUser);
        $usersSignaled=$stats->makeNumericChart($usersSignaled);

        //--------------
        return $this->json([
            'code'=>200,
            //infos générales
            'users'=>$tabUsers,
            'topics'=>$tabTopics,
            'reactions'=>$tabReactions,
            'authorsTopics' => $tabAuthorsTopics,
            'authorsReactions' => $tabAuthorsReactions,
            'topicsImplication' =>$topicsImplication,
            'reactionsImplication' =>$reactionsImplication,
            'year' =>$year,
            'topicsByCategory' =>$topicsByCategory,
            'reactionsByCategory' =>$reactionsByCategory,
            //camembert :
            'topicsCreatorsList' =>$topicsCreatorsList,
            'reactionsCreatorsList' =>$reactionsCreatorsList,
            'usersLiked' =>$usersLiked,
            'usersSignaleurs' =>$usersSignaleurs,
            'usersSignaled' =>$usersSignaled,
        ],200);
    }


    //--RECUPERATION pour tableaux News, Films, Music      
    /**
     * @Route("/admin/{slug}", name="admin_content", requirements={"slug"="news|films|music"})
     */
    public function showContent(FilmRepository $repoFilm, NewsRepository $repoNews, MusicRepository $repoMusic, Request $request, PaginatorInterface $paginator, Assistant $assistant){

        //recuperation des routes en fonction de la catégorie d'appartenance du critic (news, film, music)
        $slug=$request->attributes->get('slug');
        $items='';
        $sectionSelect='';
        $rubrique='';

        switch ($slug) {
            case 'news':
                $items=$repoNews->findAll();
                $sectionSelect="/admin/content/section-news.html.twig"; 
                $rubrique='News';
                break;
            case 'films':
                $items=$repoFilm->findAll();  
                $sectionSelect="/admin/content/section-films.html.twig"; 
                $rubrique='Films';  
                 break;       
            case 'music':
                $items=$repoMusic->findAll();
                $sectionSelect="/admin/content/section-music.html.twig"; 
                $rubrique='Albums';  
                 break;          
        }
        //--------

        $items = $paginator->paginate(
            $assistant->orderByDateDesc($items),
            $request->query->getInt('page', 1)/*page number*/,
            10/*limit per page*/
        );

        return $this->render('admin/index.html.twig',[
            'items'=>$items,
            'sectionSelect'=>$sectionSelect,
            'refPaginator'=>$items,
            'rubrique'=>$rubrique,          
        ]);
    }  

    //pour supprimer un utilisateur
    /**
     * @Route("/admin/user/{id<\d+>}/suppression", name="admin_user_remove")
     */
     public function removeUser(User $user, ObjectManager $manager){
        
        $manager->remove($user);
        $manager->flush();       
     
        return $this->redirectToRoute('admin');
     } 
   
    //barre de recherche d'un user
    /**
     * @Route("/admin/user/search", name="admin_user_search")
     */
    public function searchUser(UserRepository $repo){

        $user=$_POST['username'];
        $users=$repo->findAll();

        $usersMatch=[];

        if(strlen($user)>0){
            foreach ($users as $u) {

               if (preg_match('#^'.strtoupper($user).'#', strtoupper($u->getUsername()))){ 
                    $usersMatch[]=$u->getUsername();
                }
            }
        }
       
        #maj json pour affichage
        return $this->json([
            'code'=>200,
            'users'=>$usersMatch,
        ],200);


    }

    //route empruntée quand on appuie sur le bouton "Chercher profil utilisateur"
    /**
     * @Route("/admin/profil/search/{username}", name="admin_profil_search")
     */
    public function searchProfil(User $user = null){ 

        if(!$user){
            return $this->redirectToRoute('admin');
        }

        return $this->redirectToRoute('profil_public',[
            'username'=>$user->getUsername(),
        ]);
    }

    //pour showLogos() et showBackground()
    public function managerShow ($repo,$parent,$childRef,$indice,$fileUploader,$formType,$request,$manager){
                           
            $MasterDb=$repo->findAll();

            $assistant =new Assistant();
            $actions=$assistant->getDataForShow($indice);
            
            $actionsParent=$actions['parent'];
            $actionsChildren=$actions['children'];
            $typesChildren=$actionsChildren['types'];
            $render=$actions['render'];
        
            $children=[];
            
            foreach($typesChildren as $type){
                $child= clone($childRef);
                $func=$actionsChildren['setType'];
                $child->$func($type); 
                $func=$actionsParent['addChild'];     
                $parent->$func($child);
            }
            
            if(count($MasterDb)>0){ 
                $parent=$MasterDb[0]; //1 seule ligne dans table de masterbackground ==> pour les 3 seules lignes de background 
                $func=$actionsParent['getChildren'];     
                $children=$parent->$func();      
            }        
          
            $form=$this->createForm($formType,$parent);
            $form->handleRequest($request);  

            if($form->isSubmitted() && $form->isValid()){

                //gestion des images
                $images=$request->files->get($actions['files']['parent'])[$actions['files']['children']];      
                $i=0;

                foreach ($images as $img){

                    if($img['image']){ //si image à recuperer on l'upload et on recupere son filename pour son entité
                        $file =$img['image'];
                        $fileName = $fileUploader->uploadImage($file,'design');                
                        $children[$i]->setImage($fileName);
                    }

                    $i+=1;
                }            
                //-------------------

                //enregistrement :
                foreach ($children as $child) {
                    $child->setCreatedAt(new \Datetime());
                }
                         
                $manager->persist($parent);
                $manager->flush(); 

                return $this->redirectToRoute($actions['form']['redirection']);            
            }      

            return $this->render('admin/index.html.twig', [
                'sectionSelect'=>$render['section'],
                'rubrique'=>$render['rubrique'],
                'form'=>$form->createView(),                                  
            ]);       
    }  

    //pour removeLogo() et removeBackground()
    public function managerRemove($repo, $indice, $request, $manager){
        
        $assistant =new Assistant();
        $actions=$assistant->getDataForShow($indice);
        $actionsParent=$actions['parent'];

        //on recupère le bg/logo via masterbg/masterlogos, et la position de bg/logo dans masterbg/masterlogos
        $name=$request->attributes->get('name');

        $MasterDb=$repo->findAll();
        
       if(count($MasterDb)>0){ 
            $master=$MasterDb[0]; //1 seule ligne dans table du master  
            $func=$actionsParent['getChildren']; 
            $imgs=$master->$func();
            $img=$imgs[$name]; 
            $img->setImage(null); 

            $manager->persist($img);
            $manager->flush();            
        }
 
        $key=$actions['files']['parent'].'_'.$actions['files']['children'].'_'.$name.'_image';  
     
        return $this->json([
            'code'=>200,
            'key'=>$key,
        ],200);
    }

//----------------------------------------------------------------------- 
}

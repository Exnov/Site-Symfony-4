<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;

//----------------
use App\Entity\Critic;
use App\Form\CriticType;
use App\Repository\CriticRepository;

use App\Entity\CriticSignalement;
use App\Form\CriticSignalementType;

use App\Entity\CriticLike;
use App\Repository\CriticLikeRepository;
//----------------
use App\Repository\FilmRepository;
use App\Repository\NewsRepository;
use App\Repository\MusicRepository;
//----------------

class CriticController extends AbstractController
{

	//edition d'un commentaire (modification/suppression)
    /**
     * @Route("/{slug}/commentaire/{id<\d+>}/edit", name="critic_comment_edit", requirements={"slug"="news|film|music"})
     */
    public function editComment(Critic $critic, Request $request, ObjectManager $manager, NewsRepository $repoNews, FilmRepository $repoFilm, MusicRepository $repoMusic){ 

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED'); 

        //on verifie ensuite si l'user est bien l'auteur du critic à éditer :
        $user = $this->getUser();
        $author = $critic->getAuthor();

        if($user==$author){

            //recuperation des routes en fonction de la catégorie d'appartenance du critic (news, film, music)
            $category=$critic->getCategory();
            $routeRedirection='';
            $repo='';
            $item='';
            switch ($category) {
    		    case 'film':
    		        $routeRedirection='film_show';
                    $repo=$repoFilm;
                    break;
    		    case 'news':
    		        $routeRedirection='news_show';
                    $repo=$repoNews;
                     break;
    		    case 'music':
                    $repo=$repoMusic;
    		        $routeRedirection='music_show';
                     break;
    		}

            $item=$repo->findOneBy(['id'=>$critic->getItemId()]);
            //--------
            $form=$this->createForm(CriticType::class,$critic);

            $form->handleRequest($request);

            if($form->isSubmitted() && $form->isValid()){

                $manager->persist($critic);
                $manager->flush();

                //redirection vers le même film
                return $this->redirectToRoute($routeRedirection,[
                    'id'=>$critic->getItemId(),
                    'titleUrl'=>$item->getTitleUrl(),
                ]);
            }

            return $this->render('critic/edit-comment.html.twig',[
                'criticForm'=>$form->createView(),
                'critic'=>$critic,
            ]);
        }

        //sinon retour à l'accueil :
        else{
            return $this->redirectToRoute('home');
        }
    } 
    
    //suppression d'un commentaire de film
    /**
     * @Route("/{slug}/commentaire/{id<\d+>}/suppression", name="critic_comment_remove", requirements={"slug"="news|film|music"})
     */
    public function removeComment(Critic $critic, ObjectManager $manager, NewsRepository $repoNews, FilmRepository $repoFilm, MusicRepository $repoMusic){

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        //on verifie ensuite si l'user est bien l'auteur du critic à éditer :
        $user = $this->getUser();
        $author = $critic->getAuthor();

        if($user==$author){        

            //recuperation des routes en fonction de la catégorie d'appartenance du critic (news, film, music)
            $category=$critic->getCategory();
            $routeRedirection='';
            $routeRemove='';
            $repo='';
            $item='';
            switch ($category) {
                case 'film':
                    $routeRedirection='film_show';
                    $repo=$repoFilm;
                    break;
                case 'news':
                    $routeRedirection='news_show';
                    $repo=$repoNews;
                     break;
                case 'music':
                    $routeRedirection='music_show';
                    $repo=$repoMusic;
                     break;
            }

            $criticId=$critic->getItemId();
            $item=$repo->findOneBy(['id'=>$criticId]);
            //--------    
            $manager->remove($critic);
            $manager->flush();

            //redirection vers le même film
            return $this->redirectToRoute($routeRedirection,[
                'id'=>$criticId,
                'titleUrl'=>$item->getTitleUrl(),
            ]);
        }

        //sinon retour à l'accueil :
        else{
            return $this->redirectToRoute('home');
        }
    } 

 	//retourne page de signalement d'un commentaire de film
    /**
     * @Route("/{slug}/commentaire/{id<\d+>}/signalement", name="critic_comment_signal", requirements={"slug"="news|film|music"})
     */
    public function signalComment(Critic $critic, FilmRepository $repoFilm, NewsRepository $repoNews, MusicRepository $repoMusic, Request $request, ObjectManager $manager){

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED'); 

        $user = $this->getUser();

        //on verifie si l'user ne signale pas son propre commentaire :
        $author=$critic->getAuthor();
        if($author==$user){
            return $this->redirectToRoute('home');
        }

        //on verifie ensuite si l'user n'a pas déjà signalé le commentaire :       
        $signals=$critic->getCriticSignalements();
        $alreadySignaled=false;
        foreach ($signals as $signal) {
            if($user==$signal->getUser()){
                return $this->redirectToRoute('home');
            }
        }

        //recuperation des routes en fonction de la catégorie d'appartenance du critic (news, film, music)
        $category=$critic->getCategory();
        $idItem=$critic->getItemId();
        $routeRedirection='';
        $item='';
        switch ($category) {
		    case 'film':
		        $routeRedirection='film_show';
		        $item=$repoFilm->findOneBy(['id' => $idItem]); 
                break;
		    case 'news':
		        $routeRedirection='news_show';
		        $item=$repoNews->findOneBy(['id' => $idItem]);
                break; 
		    case 'music':
		        $routeRedirection='music_show';
		        $item=$repoMusic->findOneBy(['id' => $idItem]); 
                break;
		}        
        //--------         
       
        $signalement=new CriticSignalement();
        $form=$this->createForm(CriticSignalementType::class,$signalement);

        $form->handleRequest($request);
        $user = $this->getUser();

        if($form->isSubmitted() && $form->isValid()){
      
            $signalement->setUser($user)
                    ->setCreatedAt(new \Datetime())
                    ->setCritic($critic)
                    ;

            $manager->persist($signalement);
            $manager->flush();

            //redirection vers le même film
            return $this->redirectToRoute($routeRedirection,[
                'id'=>$idItem,
                'titleUrl'=>$item->getTitleUrl(),
            ]);
        }
        
        return $this->render('critic/signal-comment.html.twig',[
                'signalementForm'=>$form->createView(),
                'critic'=>$critic,
                'item'=>$item, //'film'=>$film,
                'routeRedirection'=>$routeRedirection,
        ]);
       
    }   

	//-------------------------------------------------------------------------------------------------------------
    /**
     * Permet de liker ou unliker un article
     *
     * @Route("/{slug}/commentaire/{id<\d+>}/like", name="critic_comment_like", requirements={"slug"="news|film|music"})
     *
     */
    public function likeComment(Critic $critic, ObjectManager $manager, CriticLikeRepository $likeRepo){

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED'); 

        $user=$this->getUser();

        #si user connecté
        #si le like existe déjà, suppression du like
        if($critic->isLikedByUser($user)){
        #retrouvé le like par rapport à l'article et l'user courant             
            $like=$likeRepo->findOneBy([
                'critic'=>$critic,
                'user'=>$user
            ]);

            $manager->remove($like);
            $manager->flush();

            #maj json pour affichage
            return $this->json([
                'code'=>200,
                'message'=>'Like bien supprimé',
                'likes'=>$likeRepo->count(['critic'=>$critic])
            ],200);
        }

        #sinon, nouveau like, on l'ajoute alors
        $like=new CriticLike();
        $like->setCritic($critic)
            ->setUser($user)
        ;
        $manager->persist($like);
        $manager->flush();

        return $this->json([
            'code'=>200,
            'message'=>'Like bien ajouté',
            'likes'=>$likeRepo->count(['critic'=>$critic])
        ],200);
    }

    //-------------------------------------------------------------------------------------------------------------
}

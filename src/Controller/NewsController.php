<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\News;
use App\Form\NewsType;
use App\Repository\NewsRepository;
use App\Repository\HomepageRepository; //apparence de la page d'accueil
use App\Repository\UserByeRepository; //autorm user
//----------------
use App\Entity\Critic;
use App\Form\CriticType;
use App\Repository\CriticRepository;
//----------------

use Symfony\Component\HttpFoundation\File\Exception\FileException; //image
use App\Service\FileUploader; //image
use  Knp\Component\Pager\PaginatorInterface;

use  App\Service\Assistant; //regroupe différentes fonctions partagées par les controllers

class NewsController extends AbstractController
{


	/**
	 * @Route("/", name="home")
	 */
    public function home(NewsRepository $repoNews, HomepageRepository $repoHomepage, Assistant $assistant, UserByeRepository $repoBye, ObjectManager $manager){

        //--autorm ckeck
        $bye=$repoBye->findAll();
        if($bye){
            foreach ($bye as $demand) {
                $user=$demand->getUser();
                $manager->remove($user); 
                $manager->flush();                
            }
        }
        //----

        //news :
        $listNews=$repoNews->findBy(
            ['status'=>'1']
        );           
        $listNews=$assistant->orderByDateDesc($listNews,true);

        //on ne garde que 9 elts :
        $qtt=9;
        if(count($listNews)<9){ //si moins de 9 news enregistrées, on recupère toutes les news
            $qtt=count($listNews);
        }
        $topNews=array_slice($listNews,0,$qtt);

        //apparence :
        $homepageDb=$repoHomepage->findAll();
        $homepage=null; //si aucune donnée enregistrée
        if(count($homepageDb)>0){ //1 seule ligne
            $homepage=$homepageDb[0];
        }

    	return $this->render('news/home.html.twig',[
            'topNews'=>$topNews,
            'homepage'=>$homepage,
            'navbarsticky'=>'ok',
    	]);
    }

    /**
     * @Route("/news", name="news")
     */
    public function index(NewsRepository $repo, Request $request, PaginatorInterface $paginator, Assistant $assistant)
    {

        $listNews=$repo->findBy(
            ['status'=>'1']
        );       

        $listNews = $paginator->paginate(
            $assistant->orderByDateDesc($listNews,true),
            $request->query->getInt('page', 1)/*page number*/,
            9/*limit per page*/
        );

        return $this->render('news/index.html.twig', [
            'listNews'=>$listNews,
        ]);
    }


    /**
     * @Route("admin/news/create", name="news_create")
     * @Route("admin/news/{id<\d+>}/edit", name="news_edit")
     */
    public function form(News $news = null,Request $request, ObjectManager $manager, FileUploader $fileUploader, Assistant $assistant){

        if(!$news){
            $news=new News();
        }

        //si news modification, on recupère son image pour l'afficher :
        $urlImage='';

        if($news->getId()){
                $urlImage=$news->getImage();
        }

        $user = $this->getUser();
        
        $form=$this->createForm(NewsType::class,$news);
        $form->handleRequest($request);

        //content de news
        $contentNews=$request->request->get('editor');

        //--
        $file =$request->files->get('news')['image'];
        //--

        /*
        on a desactivé dans NewsType la validation de image pour gérer la modification d'un form :
        - en effet si l'user ne charge pas de nouvelles images, le paramètre $_FILE est vide et bloque
        l'enregistrement
        - pour valider il faut donc que le $FILE soit complété, ou si ce n'est pas le cas il faut qu'une image soit
        déjà enregistrée
        */
        if($form->isSubmitted() && $form->isValid() && strlen($urlImage)>0 && strlen($contentNews)>0 || 
            $form->isSubmitted() && $form->isValid() && strlen($file)>0 && strlen($contentNews)>0){

            //si pas chargement de nouvelle image, on prend en compte l'image précédement enregistrée
            if(strlen($urlImage)>0 && $request->files->get('news')['image']==null){
                $news->setImage($urlImage);
            }

            //si chargement de nouvelle image, on prend en compte la nouvelle image chargée
            if($request->files->get('news')['image']){ //else
                $fileName = $fileUploader->uploadImage($file,'news'); 
                //--
                $fileUploader->resize(400,400,'news');
                $thumbnail=$fileUploader->getThumbnailName();
                //--                
                $news->setImage($fileName);
                $news->setThumbnail($thumbnail);        
            }
            
            //on ne crée la date qu'au 1er enregistrement de la news :
            if($news->getCreatedAt()==null){
                $news->setCreatedAt(new \Datetime());
            }   

            //si l'user choisit de publier sa news, et qu'il n'a pas renseigné une date de publication, on ajoute la date du jour; tous les contents publiés doivent avoir une date de publication
            if($news->getPublishedAt()==null && $news->getStatus()=='1'){
                $news->setPublishedAt(new \Datetime());
            } 
                         
            //--
            $titleUrl=$assistant->generateTitleForUrl($news->getTitle());
            //--        
            $news->setAuthor($user)
                 ->setContent($contentNews)
                 ->setTitleUrl($titleUrl)                 
            ;
            
            $manager->persist($news);
            $manager->flush();

            //si publication, direction la publication
            if($news->getStatus()=='1'){
                return $this->redirectToRoute('news_show',[
                    'id'=>$news->getId(),
                    'titleUrl'=>$news->getTitleUrl(),
                ]);
            }
            //si brouillon, direction le mode edit
            if($news->getStatus()=='0'){
                return $this->redirectToRoute('news_edit',[
                    'id'=>$news->getId(),           
                ]);
            }              
        }

        return $this->render('news/create.html.twig',[
            'formNews'=>$form->createView(),
            'editMode'=>$news->getId() !== null,
            'urlImage'=>$urlImage,    
            'news'=>$news,      
        ]);  
    }

    //pour supprimer une news
    /**
     * @Route("admin/news/{id<\d+>}/suppression", name="news_remove")
     */
    public function removeNews(News $news, ObjectManager $manager){
        
        $manager->remove($news);
        $manager->flush();       
     
        return $this->redirectToRoute('admin_content',[
            'slug'=>'news',
        ]);

     }   

    //page d'une news
    /**
     * @Route("/news/{id<\d+>}/{titleUrl}", name="news_show")
     */
    public function show(News $news, CriticRepository $repo, Request $request, ObjectManager $manager, PaginatorInterface $paginator, Assistant $assistant){

        //si post publié
        if($news->getStatus()=='1'){

            //on recupère les commentaires éventuels sur le news via l'objet Critic
            //on recherche les news par catégorie (ici 'news'), et par itemId .

            $critics=$repo->findBy([
                'category' => 'news',
                'itemId'=> $news->getId(),
            ]);

            $critics = $paginator->paginate(
                $critics,
                $request->query->getInt('page', 1)/*page number*/,
                10/*limit per page*/
            );        
            //------------------

            $critic=new Critic();
            $form=$this->createForm(CriticType::class,$critic);

            $form->handleRequest($request);

            $user = $this->getUser();
            if($form->isSubmitted() && $form->isValid()){
          
                $critic->setAuthor($user)
                        ->setCategory('news')
                        ->setItemId($news->getId())
                        ->setCreatedAt(new \Datetime())
                        ;

                $manager->persist($critic);
                $manager->flush();

                //redirection vers la même news
                return $this->redirectToRoute('news_show',[
                    'id'=>$news->getId(),
                    'titleUrl'=>$news->getTitleUrl(),
                ]);
            }         

            return $this->render('news/show.html.twig',[
                'news'=>$news,
                'critics'=>$critics,
                'criticForm'=>$form->createView(),  
                //meta og
                'og'=>'yes',          
            ]);

        }

        //sinon retour à l'accueil
        return $this->redirectToRoute('home');
    }

}

<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\Film;
use App\Form\FilmType;
use App\Repository\FilmRepository;

//----------------
use App\Entity\Critic;
use App\Form\CriticType;
use App\Repository\CriticRepository;
//----------------

use Symfony\Component\HttpFoundation\File\Exception\FileException; //image
use App\Service\FileUploader; //image
use App\Service\Assistant; 

use  Knp\Component\Pager\PaginatorInterface;


class FilmsController extends AbstractController
{
    /**
     * @Route("/films", name="films")
     */
    public function index(FilmRepository $repo, Assistant $assistant)
    {

        $films=$repo->findBy(
            ['status'=>'1']
        );          

        $films=$assistant->orderByYearDesc($films);

        return $this->render('films/index.html.twig', [
            'films' => $films,
        ]);

    }        

    /**
     * @Route("admin/film/create", name="film_create")
     * @Route("admin/film/{id<\d+>}/edit", name="film_edit")
     */
    public function form(Film $film = null,Request $request, ObjectManager $manager, FileUploader $fileUploader, Assistant $assistant){

        if(!$film){
            $film=new Film();
        }

        //si film modification, on recupère son image pour l'afficher :
        $urlImage='';

        if($film->getId()){
                $urlImage=$film->getImage();
        }

        $user = $this->getUser();
        
        $form=$this->createForm(FilmType::class,$film);
        $form->handleRequest($request);

        //content de film
        $contentFilm=$request->request->get('editor');    

        //--
        $file =$request->files->get('film')['image'];
        //--

        /*
        on a desactivé dans FilmType la validation de image pour gérer la modification d'un form :
        - en effet si l'user ne charge pas de nouvelles images, le paramètre $_FILE est vide et bloque l'enregistrement
        - pour valider il faut donc que le $FILE soit complété, ou si ce n'est pas le cas il faut qu'une image soit déjà enregistrée
        */
        if($form->isSubmitted() && $form->isValid() && strlen($urlImage)>0 && strlen($contentFilm)>0 || 
            $form->isSubmitted() && $form->isValid() && strlen($file)>0 && strlen($contentFilm)>0){

            //si pas chargement de nouvelle image, on prend en compte l'image précédement enregistrée
            if(strlen($urlImage)>0 && $request->files->get('film')['image']==null){
                $film->setImage($urlImage);
            }

            //si chargement de nouvelle image, on prend en compte la nouvelle image chargée
            if($request->files->get('film')['image']){ 
                $fileName = $fileUploader->uploadImage($file,'films'); 
                //--
                $fileUploader->resize(400,400,'films');
                $thumbnail=$fileUploader->getThumbnailName();
                //--
                $film->setImage($fileName)  
                     ->setThumbnail($thumbnail)
                ;    
            }

            //on ne crée la date qu'au 1er enregistrement du film :
            if($film->getCreatedAt()==null){
                $film->setCreatedAt(new \Datetime());
            }   

            //si l'user choisit de publier son film, et qu'il n'a pas renseigné une date de publication, on ajoute la date du jour; tous les contents publiés doivent avoir une date de publication
            if($film->getPublishedAt()==null && $film->getStatus()=='1'){
                $film->setPublishedAt(new \Datetime());
            }             
            
            //--
            $titleUrl=$assistant->generateTitleForUrl($film->getTitle());
            //--
            $film->setAuthor($user)
                ->setContent($contentFilm)
                ->setTitleUrl($titleUrl)
            ;
            
            $manager->persist($film);
            $manager->flush();

            //si publication, direction la publication
            if($film->getStatus()=='1'){
                return $this->redirectToRoute('film_show',[
                    'id'=>$film->getId(),
                    'titleUrl'=>$film->getTitleUrl(),
                ]);
            }
            //si brouillon, direction le mode edit
            if($film->getStatus()=='0'){
                return $this->redirectToRoute('film_edit',[
                    'id'=>$film->getId(),           
                ]);
            }             
        }

        return $this->render('films/create.html.twig',[
            'formFilm'=>$form->createView(),
            'editMode'=>$film->getId() !== null,
            'urlImage'=>$urlImage,    
            'film'=>$film,       
        ]);  
    } 
   

    //pour supprimer une fiche film
    /**
     * @Route("admin/film/{id<\d+>}/suppression", name="film_remove")
     */
     public function removeFilm(Film $film, ObjectManager $manager){
        
        $manager->remove($film);
        $manager->flush();       
     
        return $this->redirectToRoute('admin_content',[
            'slug'=>'films',
        ]);

     }       

    //page d'un film
    //gère aussi le forumulaire de commentaires du film    
    /**
     * @Route("/film/{id<\d+>}/{titleUrl}", name="film_show")
     */
    public function show(Film $film, CriticRepository $repo, Request $request, ObjectManager $manager, PaginatorInterface $paginator, Assistant $assistant){


        //si post publié
        if($film->getStatus()=='1'){        

            //on recupère les commentaires éventuels sur le film via l'objet Critic
            //on recherche les films par catégorie (ici 'film'), et par itemId.

            $critics=$repo->findBy([
                'category' => 'film',
                'itemId'=> $film->getId(),
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
                        ->setCategory('film')
                        ->setItemId($film->getId())
                        ->setCreatedAt(new \Datetime())
                        ;

                $manager->persist($critic);
                $manager->flush();

                //redirection vers le même film
                return $this->redirectToRoute('film_show',[
                    'id'=>$film->getId(),
                    'titleUrl'=>$film->getTitleUrl(), 
                ]);
            }        

            return $this->render('films/show.html.twig',[
                'film'=>$film,
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

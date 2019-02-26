<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\Music;
use App\Form\MusicType;
use App\Repository\MusicRepository;

//----------------
use App\Entity\Critic;
use App\Form\CriticType;
use App\Repository\CriticRepository;
//----------------

use Symfony\Component\HttpFoundation\File\Exception\FileException; //image
use App\Service\FileUploader; //image
use App\Service\Assistant; 

use  Knp\Component\Pager\PaginatorInterface;

class MusicController extends AbstractController
{
    /**
     * @Route("/music", name="music")
     */
    public function index(MusicRepository $repo, Assistant $assistant){

        $music=$repo->findBy(
            ['status'=>'1']
        );     

        $music=$assistant->orderByYearDesc($music);

        return $this->render('music/index.html.twig', [
        	'music'=>$music,
        ]);
    }

	/**
     * @Route("admin/music/create", name="music_create")
     * @Route("admin/music/{id<\d+>}/edit", name="music_edit")
     */
    public function form(Music $music = null,Request $request, ObjectManager $manager, FileUploader $fileUploader, Assistant $assistant){

        if(!$music){
            $music=new Music();
        }

        //si music modification, on recupère son image pour l'afficher :
        $urlImage='';

        if($music->getId()){
                $urlImage=$music->getImage();
        }

        $user = $this->getUser();
        
        $form=$this->createForm(MusicType::class,$music);
        $form->handleRequest($request);

        //content de music
        $contentMusic=$request->request->get('editor');
        //--
        $file =$request->files->get('music')['image'];
        //--

        /*
        on a desactivé dans MusicType la validation de image pour gérer la modification d'un form :
        - en effet si l'user ne charge pas de nouvelles images, le paramètre $_FILE est vide et bloque
        l'enregistrement
        - pour valider il faut donc que le $FILE soit complété, ou si ce n'est pas le cas il faut qu'une image soit
        déjà enregistrée
        */
        if($form->isSubmitted() && $form->isValid() && strlen($urlImage)>0 && strlen($contentMusic)>0 || 
            $form->isSubmitted() && $form->isValid() && strlen($file)>0 && strlen($contentMusic)>0){

            //check playlist--------------------------------------------
             $this->checkPlayList($request, $music);
            //------------------------------------------------------------            

            //si pas chargement de nouvelle image, on prend en compte l'image précédement enregistrée
            if(strlen($urlImage)>0 && $request->files->get('music')['image']==null){
                $music->setImage($urlImage);
            }

            //si chargement de nouvelle image, on prend en compte la nouvelle image chargée
            if($request->files->get('music')['image']){ 
                $fileName = $fileUploader->uploadImage($file,'music'); 
                //--
                $fileUploader->resize(400,400,'music');
                $thumbnail=$fileUploader->getThumbnailName();
                //--
                $music->setImage($fileName)  
                      ->setThumbnail($thumbnail)
                ;    
            }

            //on ne crée la date qu'au 1er enregistrement de music :
            if($music->getCreatedAt()==null){
                $music->setCreatedAt(new \Datetime());
            }   

            //si l'user choisit de publier son music, et qu'il n'a pas renseigné une date de publication, on ajoute la date du jour; tous les contents publiés doivent avoir une date de publication
            if($music->getPublishedAt()==null && $music->getStatus()=='1'){
                $music->setPublishedAt(new \Datetime());
            }             
            
            //--
            $titleUrl=$assistant->generateTitleForUrl($music->getTitle());
            //--            
            $music->setAuthor($user)
                  ->setContent($contentMusic)
                  ->setTitleUrl($titleUrl)                  
            ;
        
            $manager->persist($music);
            $manager->flush();

            //si publication, direction la publication
            if($music->getStatus()=='1'){
                return $this->redirectToRoute('music_show',[
                    'id'=>$music->getId(),
                    'titleUrl'=>$music->getTitleUrl(),                
                ]);
            }
            //si brouillon, direction le mode edit
            if($music->getStatus()=='0'){
                return $this->redirectToRoute('music_edit',[
                    'id'=>$music->getId(),           
                ]);
            }           
        }

        return $this->render('music/create.html.twig',[
            'formMusic'=>$form->createView(),
            'editMode'=>$music->getId() !== null,
            'urlImage'=>$urlImage,    
            'music'=>$music,       
        ]);  
    }    

    //pour supprimer une fiche album
    /**
     * @Route("admin/music/{id<\d+>}/suppression", name="music_remove")
     */
     public function removeMusic(Music $music, ObjectManager $manager){
        
        $manager->remove($music);
        $manager->flush();       
     
        return $this->redirectToRoute('admin_content',[
            'slug'=>'music',
        ]);

     } 

    //page d'un album
    /**
     * @Route("/music/{id<\d+>}/{titleUrl}", name="music_show")
     */
    public function show(Music $music, CriticRepository $repo, Request $request, ObjectManager $manager, PaginatorInterface $paginator, Assistant $assistant){

        //si post publié
        if($music->getStatus()=='1'){  

            //on recupère les commentaires éventuels sur le music via l'objet Critic
            //on recherche les musics par catégorie (ici 'music'), et par itemId .
            $critics=$repo->findBy([
                'category' => 'music',
                'itemId'=> $music->getId(),
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
                        ->setCategory('music')
                        ->setItemId($music->getId())
                        ->setCreatedAt(new \Datetime())
                        ;

                $manager->persist($critic);
                $manager->flush();

                //redirection vers la même news
                return $this->redirectToRoute('music_show',[
                    'id'=>$music->getId(),
                    'titleUrl'=>$music->getTitleUrl(), 
                ]);
            }         

            return $this->render('music/show.html.twig',[
                'music'=>$music,
                'critics'=>$critics,
                'criticForm'=>$form->createView(),  
                //meta og
                'og'=>'yes',                            
            ]);
        }

        //sinon retour à l'accueil
        return $this->redirectToRoute('home');          

    }      

    //Pour form(), l'enregistrement de la playlist
    public function checkPlayList(Request $request, Music $music){
        if($request->request->get('music')['playlist']!=null){

        	$urlPlaylist=$request->request->get('music')['playlist'];
            /*
            ref :https://www.youtube.com/watch?v=NIRLs_b5tcw&list=PLBED2388701F7D060
            ref :https://www.youtube.com/watch?v=PN0t4hBqYEw&list=PLz1nEgzkFecqLIa7HkpV4cEQQRMUzglnN
            code à garder : après le 1er = et avant le &
            */
            $regex="#^https://www.youtube.com/watch\?v=#";
        	//on verifie qu'on recupere bien une playlist YouTube
			if(preg_match($regex, $urlPlaylist)){						
				//on découpe le code src de la playlist pour la bdd après le 1er = :
				$pos = strpos($urlPlaylist, '=')+1;
				$code=substr($urlPlaylist,$pos);
                //on decoupe encore pour garder le code uniquemement avant & ; ex : NIRLs_b5tcw
                $lastpos = strpos($code, '&');
                $code=substr($urlPlaylist,$pos,$lastpos);               
				$music->setPlaylist($code);
			}
        }
    }
    //----------------------
}

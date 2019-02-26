<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;

use App\Entity\Mentions;
use App\Form\MentionsType;
use App\Repository\MentionsRepository;

use App\Entity\Bio;
use App\Form\BioType;
use App\Repository\BioRepository;

use App\Entity\Contact;
use App\Form\ContactType;
use App\Repository\ContactRepository;

use App\Repository\UserRepository;

class PagesController extends AbstractController //gestion ici des pages Bio, Mentions légales
{

    /**
     * @Route("/{slug}", name="page_show", requirements={"slug"="bio|mentions"})
     */
    public function show(MentionsRepository $repoMentions, BioRepository $repoBio, Request $request){

    	$data=$this->getDataItemForPages($repoMentions,$repoBio,$request);

    	$slug=$data['slug'];
    	$rubrique=$data['rubrique'];
    	$itemDb=$data['itemDb'];

    	$item='';
    	if(count($itemDb)>0){ //au tout 1er lancement la table est vide
    		$item=$itemDb[0];
    	}        	

        return $this->render('pages/show.html.twig', [
        	'item'=> $item,
            'rubrique' => $rubrique,
        ]);
    } 

	/**
     * @Route("admin/{slug}/edit", name="page_edit", requirements={"slug"="bio|mentions"})
     */
    public function form(MentionsRepository $repoMentions, BioRepository $repoBio, Request $request, ObjectManager $manager){

    	$data=$this->getDataItemForPages($repoMentions,$repoBio,$request);

    	$slug=$data['slug'];
    	$rubrique=$data['rubrique'];
    	$itemDb=$data['itemDb'];
    	$item="";

		switch (count($itemDb)) {
		    case 0: //au tout 1er lancement, quand le contenu de la page bio n'a pas encore été edité
		        $item=$data['entity'];
		        break;
		    default:
		       $item=$itemDb[0]; //la table bio ne contiendra qu'1 seule ligne
		}  
	
		//form
        $user = $this->getUser();        
        $form=$this->createForm($data['formType'],$item);
        $form->handleRequest($request);	

        //content 
        $contentItem=$request->request->get('editor');

        if($form->isSubmitted() && $form->isValid() && strlen($contentItem)>0){

            $item->setAuthor($user)
                 ->setCreatedAt(new \Datetime())
                 ->setContent($contentItem)
            ;
           
            $manager->persist($item);
            $manager->flush();

            return $this->redirectToRoute('page_show',[
            	'slug'=>$slug,
            ]);
        }

        return $this->render('pages/edit.html.twig',[
     		'formItem' => $form->createView(),
     		'item'=>$item,
     		'rubrique'=>$rubrique,
        ]);  
    }      

	//--
    public function getDataItemForPages(MentionsRepository $repoMentions, BioRepository $repoBio, Request $request){

        $slug=$request->attributes->get('slug');
        $repoItem='';
        $tab=[
            'slug' =>$slug,
            'itemDb'=>'',
            'rubrique'=>'',
            'entity'=>'',
            'formType'=>'',
        ];

        switch ($slug) {
            case 'mentions':
                $tab['rubrique']='Mentions légales';
                $repoItem=$repoMentions;
                $tab['entity']=new Mentions();
                $tab['formType']=MentionsType::class;
                break;
            case 'bio':
                $tab['rubrique']='Bio';
                $repoItem=$repoBio;
                $tab['entity']=new Bio();
                $tab['formType']=BioType::class;                
                 break;              
        }

        $repoDb=$repoItem->findAll();
        $tab['itemDb']=$repoDb;

        return $tab;
    } 

    /**
     * @Route("/contact", name="page_contact") 
     */
    public function contact(Request $request, ObjectManager $manager, \Swift_Mailer $mailer, UserRepository $repoUser){

        $contact=new Contact();

        $confirmation=null;

        if(isset($_SESSION['confirmcontact'])){
            $confirmation="Message envoyé";
            unset($_SESSION['confirmcontact']);
        }

        $form=$this->createForm(ContactType::class,$contact);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            //envoi bdd et boite mail de l'admin :
            //envoi bdd :
            $contact->setCreatedAt(new \Datetime());
            $manager->persist($contact);
            $manager->flush();

            //---------------------------------
            //envoi mail :
            //on recupère le mail de ou des admin(s) à qui on va envoyer le mail du nouveau contact :
            $typeUser='%"ROLE_XXXXX"%';
            $admins=$repoUser->findUsersByRole($typeUser);

            foreach ($admins as $admin) {

                $message = (new \Swift_Message('Message de XXXXX'))
                    ->setFrom('XXXXXXXXXXXXXXXXXXXXXXXXXXX')
                    ->setTo($admin->getEmail())
                    ->setBody(
                        $this->renderView(
                            'emails/contact.html.twig',
                            ['contact' => $contact]
                        ),
                        'text/html'
                    )
                ;                
            }

            $mailer->send($message);
            //---------------------------------
            
            //pour message de confirmation de l'envoi           
            $_SESSION['confirmcontact']='ok';

            return $this->redirectToRoute('page_contact');          
        }

        return $this->render('pages/contact.html.twig', [
            'formContact'=>$form->createView(),
            'confirmation'=>$confirmation,
        ]);
    }       
}

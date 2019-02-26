<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\User;
use App\Form\RegistrationType;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

use Symfony\Component\Form\Extension\Core\Type\EmailType;

use App\Repository\UserRepository;
use App\Repository\ResetPasswordRepository;

use App\Form\WordpassType;

use App\Entity\ResetPassword;

class SecurityController extends AbstractController
{
    /**
     * @Route("/inscription", name="security_registration")
     */
    public function registration(Request $request, ObjectManager $manager, UserPasswordEncoderInterface $encoder){

    	$user=new User();
    	//on crée notre form, et on le relie à la classe user
    	$form=$this->createForm(RegistrationType::class,$user);

    	//on demande au form d'analyser la requete reçu, cad $request
    	$form->handleRequest($request);

    	if($form->isSubmitted() && $form->isValid()){

    		$hash=$encoder->encodePassword($user,$user->getPassword());

            $user->setRoles(['ROLE_XXXXX'])
    		      ->setPassword($hash)
                  ->setCreatedAt(new \Datetime())
            ;

    		$manager->persist($user);
    		$manager->flush();

    		//redirection vers le formulaire de login :
    		return $this->redirectToRoute('security_login');
    	}

    	return $this->render('security/registration.html.twig',[
			'form'=>$form->createView()
    	]);

    }

    /**
     * @Route("/connexion", name="security_login")
     */
    public function login(){

        $user = $this->getUser();
        //accès à la page de connexion et de reset si user non connecté
        if($user==null){      
            //-----------
            $confirmation=null;
            if(isset($_SESSION['confirmreset'])){
                $confirmation="Mot de passe réinitialisé";
                unset($_SESSION['confirmreset']);
            }
            //----------
        	return $this->render('security/login.html.twig',[
                'confirmation'=>$confirmation,            
            ]);
        }
        //sinon retour à home
        return$this->redirectToRoute('home');
    }

    /**
     * @Route("/deconnexion", name="security_logout")
     */
    public function logout(){}

    //mot de passe perdu 
    /**
    * @Route("/mot-de-passe-perdu", name="security_pwd_lost")
    */    
    public function pwdLost(UserRepository $repoUser, ResetPasswordRepository $repoResetPwd, Request $request, ObjectManager $manager, \Swift_Mailer $mailer){

        //-----------
        $confirmation=null;
        if(isset($_SESSION['confirmreset'])){
            $confirmation="Email envoyé";
            unset($_SESSION['confirmreset']);
        }
        //----------
        $email=$request->request->get('_username');
        if($email){ //si soumission avec email

            //on recherche l'user associé à cette adresse mail
            $user=$repoUser->findOneBy([
                'email'=> $email,
            ]);

            if($user){ //si l'user existe

                $token=$this->generateToken();

                //--on verifie si user n'a pas déjà demandé un reset
                $resetPassword=$repoResetPwd->findOneBy([
                    'user'=>$user,
                ]);
                //sinon on le crée :
                if($resetPassword==null){
                    $resetPassword=new ResetPassword();
                }
               
                //envoi bdd et boite mail de l'user :
                //envoi bdd :                
                $resetPassword->setUser($user)
                      ->setToken($token)
                      ->setCreatedAt(new \Datetime())
                ;       

                $manager->persist($resetPassword);
                $manager->flush();

                //envoi boite mail
                $message = (new \Swift_Message('XXXXX : réinitialisation du mot de passe'))
                    ->setFrom('XXXXXXXXXXXXXXXXXXXXXXXXXXX')
                    ->setTo($email)
                    ->setBody(
                        $this->renderView(
                            'emails/reset-pwd.html.twig',
                            ['token' => $token,
                             'username' => $user->getUsername(),
                            ]
                        ),
                        'text/html'
                    )
                ;                

                $mailer->send($message);
                //---------------------------------                

                //pour message de confirmation du reset           
                 $_SESSION['confirmreset']='ok';

                 return $this->redirectToRoute('security_pwd_lost');                                  
            }        
        }       

        return $this->render('security/pwd-lost.html.twig',[
            'confirmation'=>$confirmation,
        ]);
    }

    //-----
    //pour générer un token qui servira de paramètre à l'url de reset du mot de passe
    public function generateToken(){

        $bytes = random_bytes(10); 
        return sha1($bytes);
    }
    //----

    //mot de passe perdu 
    /**
     * @Route("/mot-de-passe-reset/{token}", name="security_pwd_reset")
     */    
    public function pwdReset(ResetPassword $resetPassword, Request $request, ObjectManager $manager, UserPasswordEncoderInterface $encoder, \Swift_Mailer $mailer){

        $user=$resetPassword->getUser();
        $email=$user->getEmail();

        //on crée notre form, et on le relie à la classe user
        $form=$this->createForm(WordpassType::class,$user);
        $form->add('email', EmailType::class);

        //on demande au form d'analyser la requete reçu, cad $request      
        $form->handleRequest($request);
       
        if($form->isSubmitted() && $form->isValid()){

            //verification du mail
            if($email==$user->getEmail()){

                //envoi bdd et boite mail de l'user :                
                //envoi bdd :maj du mot de passe :            
                $hash=$encoder->encodePassword($user,$user->getPassword());
                $user->setPassword($hash);

                $manager->persist($user);
                $manager->flush();
               
                //envoi boite mail  :
                $message = (new \Swift_Message('XXXXX : réinitialisation du mot de passe'))
                    ->setFrom('XXXXXXXXXXXXXXXXXXXXXXXXXXX')
                    ->setTo($email)
                    ->setBody(
                        $this->renderView(
                            'emails/confirm-pwd.html.twig',
                            ['username' => $user->getUsername(),
                            ]
                        ),
                        'text/html'
                    )
                ;                

                $mailer->send($message);
                //---------------------------------

                //suppression de l'objet resetPassword :    
                $resetPassword->setUser(null); //on a autorisé ResetPassword a avoir un User null; obligé de mettre à null l'user sans quoi en supprimant $resetPassword, on supprime aussi l'user associé         
                $manager->remove($resetPassword);
                $manager->flush(); 
                
                //pour message de confirmation du reset avec redirection vers la page de connexion                        
                 $_SESSION['confirmreset']='ok';  

                 return $this->redirectToRoute('security_login');                                                                
            }
        } 

        //verification de l'addresse mail fournie par l'user et comparaison au token (si bien enregistré)
        return $this->render('security/pwd-reset.html.twig',[
            'formReset'=>$form->createView(),
        ]);        
    }

}

<?php
namespace App\Service;

use App\Repository\SocialNetworkRepository;
use App\Repository\MasterBackgroundRepository;
use App\Repository\MasterLogosRepository;
use App\Repository\SeoRepository;

class Globalist{

	//social
	public $networksNav;
    public $networksFooter;

    //apparence générale
    public $bgGeneral;
    public $bgConnexion;
    public $bgRegistration;
    public $logo;
    public $favicon;

    //seo : description
    public $descrNews;
    public $descrFilms;
    public $descrMusic;
    public $descrForums;
    public $descrBio;    

    public function __construct(SocialNetworkRepository $repoSocial, MasterBackgroundRepository $repoMasterBg, MasterLogosRepository $repoMasterLogo, SeoRepository $repoSeo){

        //social
    	$this->networksNav=$repoSocial->findForNav();
    	$this->networksFooter=$repoSocial->findForFooter();

        //apparence générale
        $this->getImages($repoMasterBg,'backgrounds');
        $this->getImages($repoMasterLogo,'logos');

        //seo : description
        $this->descrNews=$this->getDescription($repoSeo, 'news');
        $this->descrFilms=$this->getDescription($repoSeo, 'films');
        $this->descrMusic=$this->getDescription($repoSeo, 'music');
        $this->descrForums=$this->getDescription($repoSeo, 'forums');
        $this->descrBio=$this->getDescription($repoSeo, 'bio');
        
    }

    public function getImages($repo,$indice){

        $masterDb=$repo->findAll(); 
        $i=0;

        //---------------
        $actions=[
            'backgrounds'=>[
                'parent'=>'getBackgrounds',
                'children'=>[
                    'setBgGeneral',
                    'setBgConnexion',
                    'setBgRegistration',                    
                ],
            ],
            'logos'=>[
                'parent'=>'getLogos',
                'children'=>[
                    'setLogo',
                    'setFavicon',                    
                ],                
            ],
        ];

        $actionParent=$actions[$indice]['parent'];
        $actionsChildren=$actions[$indice]['children'];
        //------------         

        if(count($masterDb)>0){

            $bgs=$masterDb[0]->$actionParent();
            $repository='/XXXXX/public/uploads/images/design/';

            foreach ($bgs as $bg) {
                $func=$actionsChildren[$i];
                if($bg->getImage()){
                    $this->$func($repository.$bg->getImage()); 
                }
                $i+=1;
            }
        }
    }

    //------------
    //backgrounds
    public function setBgGeneral($bg){
        $this->bgGeneral=$bg;
    }

     public function setBgConnexion($bg){
        $this->bgConnexion=$bg;
    }
    
    public function setBgRegistration($bg){
        $this->bgRegistration=$bg;
    }    

    //logos 
    public function setLogo($img){
        $this->logo=$img;
    }

     public function setFavicon($img){
        $this->favicon=$img;
    }

    //seo :
    public function getDescription($repo, $page){
        $description=$repo->findOneBy(['page'=>$page])->getDescription();
        return $description;
    }

    //------------
}
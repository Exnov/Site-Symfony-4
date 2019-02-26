<?php
namespace App\Service;


class Assistant{ //fonctions partagées par plusieurs controllers

    //décroissant : dates des plus récentes au plus anciennes
    public function orderByDateDesc($array, $datePublished=null){

        $func='getCreatedAt';
        if($datePublished!=null){
            $func='getPublishedAt';
        }

        //on récupère les dates ordonnées pour avoir une liste de reférences ordonnées des plus récentes aux plus anciennes
        $dates=[];
        foreach ($array as $elt) {
            if(!in_array($elt->$func()->getTimestamp(), $dates)){ //on ne remet pas de date qui serait déjà récupérée
                $dates[]=$elt->$func()->getTimestamp();
            }
            
        }
        rsort($dates);

        $x=[];
        foreach ($dates as $date) {
            
            foreach ($array as $elt) {
                if($elt->$func()->getTimestamp()==$date){
                    $x[]=$elt;
                }
            }
        }
    
        return $x;
    } 

    /* appelée dans FilmsController et MusicController */
    public function orderByYearDesc($array){

        //on récupère les années ordonnées pour avoir une liste de reférences ordonnées des plus récentes aux plus anciennes
        $years=[];
        foreach ($array as $elt) {
            if(!in_array($elt->getYear(), $years)){ //on ne remet pas d'année qui serait déjà récupérée
                $years[]=$elt->getYear();
            }
            
        }
        rsort($years);

        $x=[];
        foreach ($years as $year) {
            
            foreach ($array as $elt) {
                if($elt->getYear()==$year){
                    $x[]=$elt;
                }
            }
        }
    
        return $x;
    }

    //appelée dans AdminController pour  showHomepage() :
    //vérifie si les extensions des urls indiquent des images, sans quoi les urls deviennent les extensions des formats différents
    public function checkExtension($url){

        $extensionsImage=['jpeg', 'jpg', 'png', 'bmp', 'gif'];
        $indexDot = strpos($url, '.');    
        $extension=strtolower(substr($url,$indexDot+1));
        $check=false;
        foreach ($extensionsImage as $extensionImage) {
            if($extension==$extensionImage){
                $check=true;
            }
        }
        if(!$check){
            $url=$extension;
        }

        return $url;
    }   

    //appelée dans AdminController pour  managerShow() :    
    public function getDataForShow($indice){

        $dicoActions=[
            //backgrounds
            'backgrounds'=>[
            
                'parent'=>[
                    'addChild'=> 'addBackground',
                    'getChildren'=> 'getBackgrounds',
                ],
                'children'=>[
                    'setType'=> 'setWall',
                    'types'=>[
                        'general',
                        'connexion',
                        'registration',
                    ],
                ],
                'form'=>[
                    'redirection'=>'admin_background',
                ],
                'files'=>[
                    'parent'=>'master_background',
                    'children'=>'backgrounds',
                ],
                'render'=>[
                    'section'=>'/admin/section-bg.html.twig',
                    'rubrique'=>'Backgrounds',
                ],
                
            ],
            //logos
            'logos'=>[
            
                'parent'=>[
                    'addChild'=> 'addLogo',
                    'getChildren'=> 'getLogos',
                ],
                'children'=>[
                    'setType'=> 'setType',
                    'types'=>[
                        'logo',
                        'favicon',
                    ],
                ],
                'form'=>[
                    'name'=>'MasterLogosType',
                    'redirection'=>'admin_logos',
                ],
                'files'=>[
                    'parent'=>'master_logos',
                    'children'=>'logos',
                ],
                'render'=>[
                    'section'=>'/admin/section-logos.html.twig',
                    'rubrique'=>'Logo/favicon',
                ],
                
            ],          
        ];
        //---------------

        return $dicoActions[$indice];
    }

    //pour les articles des contents (news, films, music) et forum : generer le titre de l'article à afficher dans l'url
    public function generateTitleForUrl($title){

        //supprimer les caractères spéciaux
        $titleUrl=str_replace(' ','-',$title);
        //les accents sur les voyelles : éèàêâùïüë...
        $voyellesTab=[
            ['e'=>
                ['é','è','ê','ë'],
            ],
            ['a'=>
                ['à','â','ä'],
            ],
            ['u'=>
                ['ù','ü','û'],
            ],
            ['i'=>
                ['ï','î'],
            ],
            ['o'=>
                ['ô','ö'],
            ],
        ];

        foreach ($voyellesTab as $voyelleTab) {         
            foreach ($voyelleTab as $voyelle => $accents) {     
                foreach ($accents as $accent) {
                        $titleUrl=str_replace($accent,$voyelle,$titleUrl);
                }               
            }
        }

        //on met en minuscule :
        $titleUrl=strtolower($titleUrl);

        //suppressions des caractères spéciaux par du vide
        $titleUrl = preg_replace('/[^a-z0-9\-]/', '', $titleUrl); //ne conserve que les lettres et les chiffres, on remplace ce qui est autre chose que des lettres et des chiffres par ''

        //si 1er ou dernier caractère du titre est un tiret, on supprime le tiret
    
        if($titleUrl[0]=='-'){ //1er caractère
             $titleUrl=substr($titleUrl, 1); 
        }

        $lastIndex=strlen($titleUrl)-1;
        if($titleUrl[$lastIndex]=='-'){ //dernier caractère
             $titleUrl=substr($titleUrl,0,$lastIndex-1); 
        }
        //--
        
        return $titleUrl;
    }

    //---------------    
}
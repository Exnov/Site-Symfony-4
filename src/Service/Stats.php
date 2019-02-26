<?php
namespace App\Service;



class Stats{ //appelée dans AdminController

    //recupere pour graph() les quantités de données d'une entité pour une année, et rangées par mois 
    public function getNbContentGraph($datas){

        //construire un array pour ranger les donnees par mois.
        $tab=[
            '01'=> 0,'02'=> 0,'03'=> 0,'04'=> 0,
            '05'=> 0,'06'=> 0,'07'=> 0,'08'=> 0,
            '09'=> 0,'10'=> 0,'11'=> 0,'12'=> 0,
        ];

        foreach ($datas as $data) {
            //on recupere les donnees pour chaque mois
            $month=$data->getCreatedAt()->format('m');
            //on incrémente le nombre de donnees pour chaque mois
            $tab[$month]=$tab[$month]+=1; 
        }   

        return $tab;
    }

    //recupere pour graph() les quantités de créateurs de topics et de reactions pour une année, et rangées par mois 
    public function getNbCreatorGraph($datas){

        //construire un array pour ranger les donnees par mois.      
        $tab=[
            '01'=> [],'02'=> [],'03'=> [],'04'=> [],
            '05'=> [],'06'=> [],'07'=> [],'08'=> [],
            '09'=> [],'10'=> [],'11'=> [],'12'=> [],
        ];
        
        $tab2=[
            '01'=> 0,'02'=> 0,'03'=> 0,'04'=> 0,
            '05'=> 0,'06'=> 0,'07'=> 0,'08'=> 0,
            '09'=> 0,'10'=> 0,'11'=> 0,'12'=> 0,
        ];   

        foreach ($datas as $data) {
            //on recupere les donnees pour chaque mois
            $month=$data->getCreatedAt()->format('m');
            //
            $author=$data->getAuthor()->getUsername();
            if(!in_array($author, $tab[$month])){
                array_push($tab[$month],$author); 
                $tab2[$month]=$tab2[$month]+=1;
            }
            
        }  

        return $tab2;
    }    

    //recupere pour graph() les taux d'implications des createurs sur forum (topics et messages)
    public function getTauxImplicationGraph($datas,$tabUsers,$tabCreator){

        //construire un array pour ranger les donnees par mois.
        $tab=[
            '01'=> 0,'02'=> 0,'03'=> 0,'04'=> 0,
            '05'=> 0,'06'=> 0,'07'=> 0,'08'=> 0,
            '09'=> 0,'10'=> 0,'11'=> 0,'12'=> 0,
        ];

        $i=0;
        $nUsers=0;
        $ref="";
        $tab2=$tab;

        foreach ($datas as $data) {
            //on recupere les donnees pour chaque mois
            $month=$data->getCreatedAt()->format('m');
            //la 1ère recuperation de données est égale au nombre d'utilisateurs total pour le 1er mois intégrant des données
            if($i==0){
                $nUsers=count($datas);
                $ref=$month;
            }
           
            if($ref!=$month && empty($tab[$month])){
                $prevMonth=array_search($nUsers, $tab); //on recupère le mois précédent dans le tab, mais suivant dans le temps
                $nUsers=$tab[$prevMonth]-$tabUsers[$prevMonth]; //on soustrait le nbre d'utilisateurs du mois avec le nombre d'inscriptions du mois, pour obtenir le total d'utilisateurs pour le mois précédent dans le temps
            }

            $tab[$month]=$nUsers;
            //calcul du pourcentage d'implication : 100/nbre d'utilisateur par mois * nombre de créateurs dans le mois
            if($nUsers>0){
                $tab2[$month]=round((100/$nUsers)*$tabCreator[$month],1); //on arrondit à un chiffre après la virgule
            }

            $i+=1;
        }        
        return $tab2;
    }   

    //recupére pour graph() les topics et $reactions pour une année rangés par mois et catégorie
    public function getNbContentGraphByCategory($datas,$categories,$typeEntity){

        //tabCategory : contient le nombre de contenus par catégories; tableau associatif
        $tabCategory=[];
        foreach ($categories as $category) {
            $tabCategory[$category->getCategory()]=0;
        }

        //construire un array pour ranger les donnees par mois.
        $tab=[
            '01'=> $tabCategory,'02'=> $tabCategory,'03'=> $tabCategory,'04'=> $tabCategory,
            '05'=> $tabCategory,'06'=> $tabCategory,'07'=> $tabCategory,'08'=> $tabCategory,
            '09'=> $tabCategory,'10'=> $tabCategory,'11'=> $tabCategory,'12'=> $tabCategory,
        ];
     
        foreach ($datas as $data) {
            //on recupere les donnees pour chaque mois
            $month=$data->getCreatedAt()->format('m');
            //on recupere la category de l'article :
            if($typeEntity=='topic'){
                $category=$data->getCategory()->getCategory();
            }
             if($typeEntity=='reaction'){
                $category=$data->getTopic()->getCategory()->getCategory();
            }           
            //on incrémente le nombre de donnees pour chaque mois
            $tab[$month][$category]=$tab[$month][$category]+=1; 
        }  

        //-------
        //on va chercher à regrouper pour une année les tableaux de datas par catégorie.
        //par exemple : un tableau de cinéma (en clef) regroupant les différents tableaux de datas par mois (en valeur), qu'on range dans un grand tableau à transmettre à ajax.   
        $tab2=[
            '01'=> 0,'02'=> 0,'03'=> 0,'04'=> 0,
            '05'=> 0,'06'=> 0,'07'=> 0,'08'=> 0,
            '09'=> 0,'10'=> 0,'11'=> 0,'12'=> 0,
        ];
        //----------
        //on crée un systéme de tableaux pour renvoyer à ajax des tableaux avec en clef des valeurs numériques qu'il pourra parcourir dans l'ordre (sans 01 mais avec 1, ou sans 02 mais avec 2 ...).
        $equiv=[
            '01'=> '1','02'=> '2','03'=> '3','04'=> '4',
            '05'=> '5','06'=> '6','07'=> '7','08'=> '8',
            '09'=> '9','10'=> '10','11'=> '11','12'=> '12',
        ];
        $tab3=[
            '1'=> 0,'2'=> 0,'3'=> 0,'4'=> 0,
            '5'=> 0,'6'=> 0,'7'=> 0,'8'=> 0,
            '9'=> 0,'10'=> 0,'11'=> 0,'12'=> 0,
        ];

        //----------
        $tabCategory2=[];
        foreach ($categories as $category) {
            
            $tabSupport=$tab3; //on reinitialise le tabSupport pour qu'il ne recupère pas les données des autres mois

            foreach ($tab2 as $month => $nb) {
                $tabSupport[$equiv[$month]]=$tab[$month][$category->getCategory()];
            }
            $tabCategory2[$category->getCategory()]=$tabSupport; 
        }       
        //-------

        return $tabCategory2; 
    } 

    //pour graph(), camemberts : contribution des créateurs de topics et messages, en nom et pourcentage 
    public function makePercentChart($ref,$array){
        $nbElts=count($ref);
        foreach ($array as $key => $value) {
            foreach ($value as $k => $v) {
               if($k='y'){
                   $value[$k]=round((100/$nbElts)* intval($v),1);
                   $array[$key]=$value;
               }
            }
        }
        return $array;          
    }   

    //pour graph(), camemberts, usersLikes : convertir les y en nombre : 
    public function makeNumericChart($array){
        foreach ($array as $key => $value) {
            foreach ($value as $k => $v) {
               if($k='y'){
                   $value[$k]=intval($v);
                   $array[$key]=$value;
               }
            }
        }
        return $array;          
    } 

    //pour stats(), et les infos générales, recupére en string le nbre total pour les users, topics, messages, likes, et signalement sans année précisée 
    public function getNumberOfData($array){

        $n='0';
        if(count($array)>0){
            $n=$array[0]['nbre'];
        }
        return $n;
    }   
    
    //--------  
}
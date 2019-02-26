<?php
namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploader
{
    private $targetDirectory;
    private $fileName;
    private $adresseImageRef;
    private $uniqueId;
    private $extension;
    private $source;
    private $donnees;
    private $largeur;
    private $hauteur; 
    private $thumbnailName;  
    private $directoryForResize;


    public function __construct($targetDirectory)
    {
        $this->targetDirectory = $targetDirectory;
    }

    public function uploadImage(UploadedFile $file, $fileExport=null)
    {
        /*1/2
        - pour que $this->targetDirectory reste égal à $this->targetDirectory en cas de boucle qui appelle uploadImage()
        - on garde une trace de la valeur d'orgine $this->targetDirectory
        */
        $target=$this->targetDirectory; 

        //--------------
        switch ($fileExport) {
            case 'films':
                $this->targetDirectory.='/films';
                break;
            case 'news':
                $this->targetDirectory.='/news';
                break;
             case 'music':
                $this->targetDirectory.='/music';
                break; 
             case 'homepage':
                $this->targetDirectory.='/homepage';
                break;   
             case 'design':
                $this->targetDirectory.='/design';
                break;                                                            
        }
        //---------------      

        $this->uniqueId=md5(uniqid());
        $this->extension=$file->guessExtension();

        $this->fileName= $this->uniqueId.'.'.$this->extension;
        $this->adresseImageRef=$this->targetDirectory.'/'.$this->fileName;

        try {
            $file->move($this->getTargetDirectory(), $this->fileName);

        } catch (FileException $e) {
            // ... handle exception if something happens during file upload
        }

        /*2/2
        - pour que $this->targetDirectory reste égal à $this->targetDirectory en cas de boucle qui appelle uploadImage()
        - $this->targetDirectory recupère sa valeur d'origine
        */
        $this->targetDirectory=$target;
        //----------

        return $this->fileName;
    }

    public function getTargetDirectory()
    {
        return $this->targetDirectory;
    }
    
    public function resize($largeur,$hauteur,$directoryForResize=null){ //aussi appelée dans ProfilController

        $this->directoryForResize=$this->targetDirectory;
        //--------------
        switch ($directoryForResize) {
            case 'films':
                $this->directoryForResize.='/films';
                break;
            case 'news':
                $this->directoryForResize.='/news';
                break;
             case 'music':
                $this->directoryForResize.='/music';
                break;                                                            
        }
        //---------------------            
        $this->largeur=$largeur;
        $this->hauteur=$hauteur;      
        //----------------------------
        switch ($this->extension) {
              case 'jpg':
                     $this->source = imagecreatefromjpeg($this->adresseImageRef); 
                     $this->sampleImage();
                     imagejpeg($this->donnees[0],$this->donnees[1]);
                    break;              
               case 'jpeg':
                     $this->source = imagecreatefromjpeg($this->adresseImageRef); 
                     $this->sampleImage();
                     imagejpeg($this->donnees[0],$this->donnees[1]);
                    break;
              case 'png':
                     $this->source = imagecreatefrompng($this->adresseImageRef); 
                     $this->sampleImage();
                     imagepng($this->donnees[0],$this->donnees[1]);
                    break;
              case 'gif':
                    $this->source = imagecreatefromgif($this->adresseImageRef); 
                    $this->sampleImage();
                    imagegif($this->donnees[0],$this->donnees[1]);
                    break;                             
              default:
                   # code...
                    break;              
        }
    }

    private function sampleImage(){

        $largeur_destination=$this->largeur; 
        $hauteur_destination=$this->hauteur; 

        $largeur_source = imagesx($this->source);
        $hauteur_source = imagesy($this->source);

        $absPt_source=0;
        $ordPt_source=0;

        $ratio_ref=$largeur_destination/$hauteur_destination; //5/3 = 1.66 cad du 1 (h) sur 1.66 (l)
        $ratio_orig = $largeur_source/$hauteur_source; 

        //si ratios == : on ne fait rien

        //si ratios != :
        if($ratio_ref!=$ratio_orig){

          //maj des largeur et hauteur de l'image source en fonction du ratio de ref:
          if ($ratio_ref > $ratio_orig) {
              $memo=$hauteur_source;
               $hauteur_source = round($largeur_source/$ratio_ref);
              //calcul du point de récupération de l'image source : besoin uniquement de calculer point ordonnée
              $ordPt_source=round(($memo-$hauteur_source)/2);
          } 
          else {
              $memo=$largeur_source;
              $largeur_source = round($hauteur_source*$ratio_ref);
              //calcul du point de récupération de l'image source :  besoin uniquement de calculer point abscisse
              $absPt_source=round(($memo-$largeur_source)/2);
           }

        }

        $destination = imagecreatetruecolor($largeur_destination, $hauteur_destination); 
        // On crée la miniature vide
        // Les fonctions imagesx et imagesy renvoient la largeur et la hauteur d'une image
          
        // On crée la miniature
        imagecopyresampled($destination, $this->source, 0, 0, $absPt_source, $ordPt_source,
        $largeur_destination, $hauteur_destination, $largeur_source,
        $hauteur_source);

        // On enregistre la miniature sous le nom "mini_couchersoleil.jpg"
        $this->thumbnailName=$this->uniqueId."_thumbnail.".$this->extension;
        $location=$this->directoryForResize.'/'.$this->thumbnailName; 
         
        $this->donnees=[$destination, $location];
           
    }

    public function getThumbnailName(){
        return $this->thumbnailName;
    }
        
  //--------------
}
<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('checkurlextension', [$this, 'checkUrlExtension']),
        ];
    }

    public function checkUrlExtension($image){ //boolean return

    	$check=false;
    	//si $image longueur >=32 c'est une image
	    if(strlen($image)>=32){
	    	$check=true;
	    }
	    return $check;
    }

}
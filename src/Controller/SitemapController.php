<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Response;

use App\Repository\NewsRepository;
use App\Repository\FilmRepository;
use App\Repository\MusicRepository;
use App\Repository\ForumRepository;
use App\Repository\TopicRepository;


class SitemapController extends AbstractController
{

    /**
     * @Route("/sitemap.xml", name="sitemap", defaults={"_format"="xml"})
     */
    public function show(Request $request, NewsRepository $repoNews, FilmRepository $repoFilm, MusicRepository $repoMusic, ForumRepository $repoForum, TopicRepository $repoTopic){


    	$hostname = $request->getSchemeAndHttpHost();

    	//--
    	$urls = array();
    	//pages du menu
    	$urls[] = array('loc' => $this->generateUrl('home'));
    	$urls[] = array('loc' => $this->generateUrl('news')); 
    	$urls[] = array('loc' => $this->generateUrl('films')); 
    	$urls[] = array('loc' => $this->generateUrl('music')); 
    	$urls[] = array('loc' => $this->generateUrl('forum')); 
        //bio :     
        $urls[] = array(
                'loc' => $this->generateUrl('page_show',['slug'=>'bio'])
            );        

    	// add dynamic urls, like blog posts from your DB 	
    	//CONTENT article : News, Films, Music
        //news :
        $urls=$this->makeXmlForContent($urls, $repoNews, 'news_show');

        //films :       
        $urls=$this->makeXmlForContent($urls, $repoFilm, 'film_show');

        //music :
        $urls=$this->makeXmlForContent($urls, $repoMusic, 'music_show');         

        //FORUMS :      
        //les rubriques :
        $categories=$repoForum->findAll();
        foreach ($categories as $category) {
            $urls[] = array(
                'loc' => $this->generateUrl('forum_category',['slug'=>$category->getSlug()])
            );
        }          

        //les conversations
        $topics=$repoTopic->findAll();
        foreach ($topics as $topic) {
            $urls[] = array(
                'loc' => $this->generateUrl('forum_topic',[
                    'slug'=>$topic->getCategory()->getSlug(),
                    'id'=>$topic->getId(),
                    'titleUrl'=>$topic->getTitleUrl(),   
                ])
            );
        }       

        // return response in XML format
        $response = new Response(
            $this->renderView('sitemap/sitemap.html.twig', array( 'urls' => $urls,
                'hostname' => $hostname)),
            200
        );
        $response->headers->set('Content-Type', 'text/xml');
 
        return $response;    	

    }
    //---------------------------   

    //pour les données de News, Films, et Music à ajouter au XML
    public function makeXmlForContent($urls, $repo, $route){
                
            $contents=$repo->findAll();
            foreach ($contents as $content) {
                $urls[] = array(
                    'loc' => $this->generateUrl($route,['id'=>$content->getId(),'titleUrl'=>$content->getTitleUrl()])
                );
            }           
            return $urls;       
    }
    //--------------------

}

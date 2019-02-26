<?php

namespace App\Repository;

use App\Entity\Reaction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Reaction|null find($id, $lockMode = null, $lockVersion = null)
 * @method Reaction|null findOneBy(array $criteria, array $orderBy = null)
 * @method Reaction[]    findAll()
 * @method Reaction[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReactionRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Reaction::class);
    }

    // /**
    //  * @return Reaction[] Returns an array of Reaction objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    //perso : récupérer tous les reactions en fonction d'une année précisée; pareil que pour TopicRepository
    public function findReactionsByYear($debut,$fin,$typeUser){ 
        return $this->createQueryBuilder('r')
            ->andWhere('r.createdAt >= :debut')
            ->andWhere('r.createdAt < :fin')
            ->setParameter('debut', $debut)
            ->setParameter('fin', $fin)

            ->leftJoin('r.author', 'r_author')

            ->andWhere('r_author.roles LIKE :typeUser')
            ->setParameter('typeUser', $typeUser)

            ->orderBy('r.createdAt', 'ASC')
        
            ->getQuery()
            ->getResult()
        ;
    }

    //perso : recupérer les users qui écrivent des topics dans l'année précisée
     public function findTopAuthorsInReactions($debut,$fin,$typeUser){ 
        return $this->createQueryBuilder('r')               
            ->join('r.author','author') //permet de récupérer les users via la propriété author de topic, et en créant un alias 'author'
            ->select('author.username as name, COUNT(author.username) as y')
            
            ->groupBy('author.username')              
            ->orderBy('y', 'DESC')
            
            ->andWhere('author.roles LIKE :typeUser')
            ->andWhere('r.createdAt >= :debut')
            ->andWhere('r.createdAt < :fin')               

            ->setParameter('debut', $debut)
            ->setParameter('typeUser', $typeUser)
            ->setParameter('fin', $fin)
            ->getQuery()
            ->getResult()              
        ;
    } 

    //perso : recupérer tous les users dont des messages ont été signalés dans une année précisée
    public function findUsersSignaled($debut,$fin,$typeUser){
        return $this->createQueryBuilder('r')           
            ->join('r.signalements','signals') //création d'un alias 'signals' pour 'r.signalements'
            ->join('signals.user','signaleur') //création d'un alias 'signaleur' pour les auteurs des signals
            ->join('r.author','author') //création d'un alias 'author' pour 'r.author'
            ->select('author.username as name, COUNT(signals) as y')
            
            ->groupBy('author.username')              
            ->orderBy('y', 'DESC')
            
            ->andWhere('signaleur.roles LIKE :typeUser') //on prend les rôles des auteurs des signals, mais les auteurs des reactions signalés !!!
            ->andWhere('signals.createdAt >= :debut') //on prend en compte les dates de signalements !!!
            ->andWhere('signals.createdAt < :fin')               

            ->setParameter('debut', $debut)
            ->setParameter('typeUser', $typeUser)
            ->setParameter('fin', $fin)

            ->getQuery()
            ->getResult()              
        ;
    }   

    //perso : compte le nombre de reactions écrits sauf par les ROLE XXXXX ; même dans TopicRepository
    public function countAllExceptAdmin($typeUser){
        return $this->createQueryBuilder('r')
            ->join('r.author','author') //permet de récupérer les users via la propriété author de reaction, et en créant un alias 'author'                          
            ->select('COUNT(r) as nbre')
            ->andWhere('author.roles LIKE :typeUser')
            ->setParameter('typeUser', $typeUser)
            ->getQuery()
            ->getResult()              
        ;
    } 

    //perso : compte le nombre de reactions likés sauf par les ROLE XXXXX 
    public function countAllLikesExceptAdmin($typeUser){
        return $this->createQueryBuilder('r')
            ->join('r.author','author') //permet de récupérer les users via la propriété author de reaction, et en créant un alias 'author'   
            ->join('r.reactionLikes','likes') //permet de récupérer les likes via la propriété reactionLikes de reaction, et en créant un alias 'likes'  
            ->join('likes.user','likeur') //permet de récupérer les users qui ont liké                                   
            ->select('COUNT(r) as nbre')
            
            ->andWhere('likeur.roles LIKE :typeUser') //on ne récupère que les messages likés par des ROLE XXXXX
            ->andWhere('author.roles LIKE :typeUser') //on ne récupère que les messages écrits par des ROLE XXXXX

            ->setParameter('typeUser', $typeUser)

            ->getQuery()
            ->getResult()              
        ;
    }     

    //perso : compte le nombre de reactions signalés sauf par les ROLE XXXXX 
    public function countAllSignalsExceptAdmin($typeUser){
        return $this->createQueryBuilder('r') 
            ->join('r.author','author') //permet de récupérer les users via la propriété author de reaction, et en créant un alias 'author'   
            ->join('r.signalements','signals')   
            ->join('signals.user','signaleur')     

            ->select('COUNT(r) as nbre')
            
            ->andWhere('signaleur.roles LIKE :typeUser') //on ne récupère que les messages signalés par des ROLE XXXXX
            ->andWhere('author.roles LIKE :typeUser') //on ne récupère que les messages écrits par des ROLE XXXXX

            ->setParameter('typeUser', $typeUser)

            ->getQuery()
            ->getResult()              
        ;
    }  
    
    //---------
}

<?php

namespace App\Repository;

use App\Entity\Topic;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Topic|null find($id, $lockMode = null, $lockVersion = null)
 * @method Topic|null findOneBy(array $criteria, array $orderBy = null)
 * @method Topic[]    findAll()
 * @method Topic[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TopicRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Topic::class);
    }

    // /**
    //  * @return Topic[] Returns an array of Topic objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    //perso : récupérer tous les topics en fonction d'une année précisée
    public function findTopicsByYear($debut,$fin,$typeUser){ 
        return $this->createQueryBuilder('t')
            ->andWhere('t.createdAt >= :debut')
            ->andWhere('t.createdAt < :fin')

            ->setParameter('debut', $debut)
            ->setParameter('fin', $fin)

            ->leftJoin('t.author', 't_author')

            ->andWhere('t_author.roles LIKE :typeUser')
            ->setParameter('typeUser', $typeUser)

            ->orderBy('t.createdAt', 'ASC')
        
            ->getQuery()
            ->getResult()
        ;
    }

    //perso : recupérer les users qui écrivent des topics dans l'année précisée
     public function findTopAuthorsInTopics($debut,$fin,$typeUser){ 
        return $this->createQueryBuilder('t')        
            ->join('t.author','author') //permet de récupérer les users via la propriété author de topic, et en créant un alias 'author'
            ->select('author.username as name, COUNT(author.username) as y')
            
            ->groupBy('author.username')              
            ->orderBy('y', 'DESC')
            
            ->andWhere('author.roles LIKE :typeUser')
            ->andWhere('t.createdAt >= :debut')
            ->andWhere('t.createdAt < :fin')               

            ->setParameter('debut', $debut)
            ->setParameter('typeUser', $typeUser)
            ->setParameter('fin', $fin)

            ->getQuery()
            ->getResult()              
         ;
    }   

    //perso : compte le nombre de topics écrits sauf par les ROLE XXXXX
    public function countAllExceptAdmin($typeUser){
        return $this->createQueryBuilder('t')
            ->join('t.author','author') //permet de récupérer les users via la propriété author de topic, et en créant un alias 'author'                          
            ->select('COUNT(t) as nbre')
            
            ->andWhere('author.roles LIKE :typeUser')
           
            ->setParameter('typeUser', $typeUser)

            ->getQuery()
            ->getResult()              
        ;
    } 

    //---------       
}

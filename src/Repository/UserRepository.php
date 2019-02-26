<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, User::class);
    }

    // /**
    //  * @return User[] Returns an array of User objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    //perso : trouver des users par rôle
    public function findUsersByRole($typeUser){
         return $this->createQueryBuilder('u')
            ->andWhere('u.roles LIKE :typeUser')
            ->setParameter('typeUser', $typeUser)
            ->getQuery()
            ->getResult()
        ;       
    }

    //perso : récupérer tous les users en fonction d'une année précisée
    public function findUsersByYear($debut,$fin,$typeUser){
        return $this->createQueryBuilder('u')
            ->andWhere('u.createdAt >= :debut')
            ->andWhere('u.createdAt < :fin')
            ->andWhere('u.roles LIKE :typeUser')
            ->setParameter('debut', $debut)
            ->setParameter('fin', $fin)
            ->setParameter('typeUser', $typeUser)
            ->orderBy('u.createdAt', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
 
    //perso : récupérer tous les users en fonction d'une année précisée, du plus recent au plus vieux
    public function findUsersByYearReverse($debut,$fin,$typeUser){
        return $this->createQueryBuilder('u')
            ->andWhere('u.createdAt >= :debut')
            ->andWhere('u.createdAt < :fin')
            ->andWhere('u.roles LIKE :typeUser')
            ->setParameter('debut', $debut)
            ->setParameter('fin', $fin)
            ->setParameter('typeUser', $typeUser)
            ->orderBy('u.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }  

    //perso : recupérer tous les users likés d'une année précisée
    public function findUsersLiked($debut,$fin,$typeUser){
        return $this->createQueryBuilder('u')
            ->join('u.reactionLikes','likes') //création d'un alias 'likes' pour 'u.reactionLikes'
            ->select('u.username as name, COUNT(likes) as y')
            
            ->groupBy('u.username')              
            ->orderBy('y', 'DESC')
            
            ->andWhere('u.roles LIKE :typeUser')
            ->andWhere('u.createdAt >= :debut')
            ->andWhere('u.createdAt < :fin')               

            ->setParameter('debut', $debut)
            ->setParameter('typeUser', $typeUser)
            ->setParameter('fin', $fin)

            ->getQuery()
            ->getResult()              
        ;
    }

     //perso : recupérer tous les users qui signalent dans une année précisée
    public function findUsersSignaleurs($debut,$fin,$typeUser){
        return $this->createQueryBuilder('u')
            ->join('u.signalements','signals') //création d'un alias 'signals' pour 'u.signalements'
            ->select('u.username as name, COUNT(signals) as y')
            
            ->groupBy('u.username')              
            ->orderBy('y', 'DESC')
            
            ->andWhere('u.roles LIKE :typeUser')
            ->andWhere('u.createdAt >= :debut')
            ->andWhere('u.createdAt < :fin')               

            ->setParameter('debut', $debut)
            ->setParameter('typeUser', $typeUser)
            ->setParameter('fin', $fin)

            ->getQuery()
            ->getResult()              
        ;
    }  

    //perso : compte le nombre d'utilisateurs sauf les ROLE XXXXX
    public function countAllExceptAdmin($typeUser){
        return $this->createQueryBuilder('u')                          
            ->select('COUNT(u) as nbre')
            ->andWhere('u.roles LIKE :typeUser')
            ->setParameter('typeUser', $typeUser)
            ->getQuery()
            ->getResult()              
        ;
    } 

    //---------
}

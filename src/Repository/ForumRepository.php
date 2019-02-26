<?php

namespace App\Repository;

use App\Entity\Forum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Forum|null find($id, $lockMode = null, $lockVersion = null)
 * @method Forum|null findOneBy(array $criteria, array $orderBy = null)
 * @method Forum[]    findAll()
 * @method Forum[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ForumRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Forum::class);
    }

    // /**
    //  * @return Forum[] Returns an array of Forum objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('f.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */
    //perso : récupérer toutes les catégories du forum en fonction d'une année précisée, on recupère toutes les catégories avant l'année qui suit (ex:l'user choisit 2018, on prend toutes les catégories créées avant 2019)
    public function findCategoriesByYear($fin){
        return $this->createQueryBuilder('f')
            ->andWhere('f.createdAt < :fin')
            ->setParameter('fin', $fin)
            ->orderBy('f.createdAt', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    //perso : compte le nombre de catégories
    public function countAll(){
        return $this->createQueryBuilder('f')                           
            ->select('COUNT(f) as nbre')
            ->getQuery()
            ->getResult()              
        ;
    } 

    //---------
}

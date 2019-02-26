<?php

namespace App\Repository;

use App\Entity\Corbeille;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Corbeille|null find($id, $lockMode = null, $lockVersion = null)
 * @method Corbeille|null findOneBy(array $criteria, array $orderBy = null)
 * @method Corbeille[]    findAll()
 * @method Corbeille[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CorbeilleRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Corbeille::class);
    }

    // /**
    //  * @return Corbeille[] Returns an array of Corbeille objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Corbeille
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

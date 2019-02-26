<?php

namespace App\Repository;

use App\Entity\Critic;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Critic|null find($id, $lockMode = null, $lockVersion = null)
 * @method Critic|null findOneBy(array $criteria, array $orderBy = null)
 * @method Critic[]    findAll()
 * @method Critic[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CriticRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Critic::class);
    }

    // /**
    //  * @return Critic[] Returns an array of Critic objects
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
    public function findOneBySomeField($value): ?Critic
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

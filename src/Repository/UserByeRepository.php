<?php

namespace App\Repository;

use App\Entity\UserBye;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method UserBye|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserBye|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserBye[]    findAll()
 * @method UserBye[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserByeRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, UserBye::class);
    }

    // /**
    //  * @return UserBye[] Returns an array of UserBye objects
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

    /*
    public function findOneBySomeField($value): ?UserBye
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

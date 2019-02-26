<?php

namespace App\Repository;

use App\Entity\Mentions;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Mentions|null find($id, $lockMode = null, $lockVersion = null)
 * @method Mentions|null findOneBy(array $criteria, array $orderBy = null)
 * @method Mentions[]    findAll()
 * @method Mentions[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MentionsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Mentions::class);
    }

    // /**
    //  * @return Mentions[] Returns an array of Mentions objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Mentions
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

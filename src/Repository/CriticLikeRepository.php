<?php

namespace App\Repository;

use App\Entity\CriticLike;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method CriticLike|null find($id, $lockMode = null, $lockVersion = null)
 * @method CriticLike|null findOneBy(array $criteria, array $orderBy = null)
 * @method CriticLike[]    findAll()
 * @method CriticLike[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CriticLikeRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CriticLike::class);
    }

    // /**
    //  * @return CriticLike[] Returns an array of CriticLike objects
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
    public function findOneBySomeField($value): ?CriticLike
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

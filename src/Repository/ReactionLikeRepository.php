<?php

namespace App\Repository;

use App\Entity\ReactionLike;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ReactionLike|null find($id, $lockMode = null, $lockVersion = null)
 * @method ReactionLike|null findOneBy(array $criteria, array $orderBy = null)
 * @method ReactionLike[]    findAll()
 * @method ReactionLike[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReactionLikeRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ReactionLike::class);
    }

    // /**
    //  * @return ReactionLike[] Returns an array of ReactionLike objects
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

    /*
    public function findOneBySomeField($value): ?ReactionLike
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

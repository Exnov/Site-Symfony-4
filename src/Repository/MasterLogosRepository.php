<?php

namespace App\Repository;

use App\Entity\MasterLogos;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method MasterLogos|null find($id, $lockMode = null, $lockVersion = null)
 * @method MasterLogos|null findOneBy(array $criteria, array $orderBy = null)
 * @method MasterLogos[]    findAll()
 * @method MasterLogos[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MasterLogosRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, MasterLogos::class);
    }

    // /**
    //  * @return MasterLogos[] Returns an array of MasterLogos objects
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
    public function findOneBySomeField($value): ?MasterLogos
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

<?php

namespace App\Repository;

use App\Entity\VerifyEmailRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method VerifyEmailRequest|null find($id, $lockMode = null, $lockVersion = null)
 * @method VerifyEmailRequest|null findOneBy(array $criteria, array $orderBy = null)
 * @method VerifyEmailRequest[]    findAll()
 * @method VerifyEmailRequest[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VerifyEmailRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VerifyEmailRequest::class);
    }

    // /**
    //  * @return VerifyEmailRequest[] Returns an array of VerifyEmailRequest objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('v.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?VerifyEmailRequest
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

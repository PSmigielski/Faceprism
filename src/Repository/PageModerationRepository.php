<?php

namespace App\Repository;

use App\Entity\PageModeration;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PageModeration|null find($id, $lockMode = null, $lockVersion = null)
 * @method PageModeration|null findOneBy(array $criteria, array $orderBy = null)
 * @method PageModeration[]    findAll()
 * @method PageModeration[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PageModerationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PageModeration::class);
    }
    public function getAllAdministrationQuery(string $pageId): QueryBuilder
    {
        return $this->createQueryBuilder("pm")->where("pm.pm_page = :id")->setParameter("id", $pageId);
    }

    // /**
    //  * @return PageModeration[] Returns an array of PageModeration objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?PageModeration
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

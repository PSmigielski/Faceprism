<?php

namespace App\Repository;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Post|null find($id, $lockMode = null, $lockVersion = null)
 * @method Post|null findOneBy(array $criteria, array $orderBy = null)
 * @method Post[]    findAll()
 * @method Post[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }
    public function createFindAllQuery(string $userID): QueryBuilder
    {
        return $this->createQueryBuilder("p")->leftJoin("App\Entity\Friend", "f", Join::WITH, "f.fr_friend=p.po_author")->where("f.fr_user = :id")->orderBy('p.po_created_at', "DESC")->setParameter("id", $userID);
    }
    public function createFindAllPostsForUser(string $userID): QueryBuilder
    {
        return $this->createQueryBuilder("p")->where("p.po_author = :id")->andWhere("p.po_page_id IS NULL")->orderBy('p.po_created_at', "DESC")->setParameter("id", $userID);
    }
    public function createFindAllPostsForPage(string $pageID): QueryBuilder
    {
        return $this->createQueryBuilder("p")->innerJoin("App\Entity\Page", "pa", Join::WITH, "p.po_page=pa.pa_id")->where("p.po_page = :id")->orderBy('p.po_created_at', "DESC")->setParameter("id", $pageID);
    }
    // /**
    //  * @return Post[] Returns an array of Post objects
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

    public function findOneBySomeField($value): ?Post
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

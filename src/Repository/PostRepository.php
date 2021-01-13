<?php

namespace App\Repository;

use App\Entity\Category;
use App\Entity\Post;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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

    /**
     * @return Post[]
     */
    public function findLastFive(): array
    {
        $date = new DateTime();

       return $this->createQueryBuilder('p')
            ->andWhere('p.publishedAt < :date')
            ->setParameter('date', $date)
            ->orderBy('p.publishedAt', 'DESC')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * @return Post[]
     */
    public function findAllRecent()
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * @return Post[]
     */
    public function findAllRecentPublished()
    {
        $date = new DateTime();

        return $this->createQueryBuilder('p')
            ->andWhere('p.publishedAt < :date')
            ->setParameter('date', $date)
            ->orderBy('p.publishedAt', 'DESC')
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * @return Post[]
     */
    public function findAllRecentPublishedByCategory(Category $category)
    {
        $date = new DateTime();

        return $this->createQueryBuilder('p')
            ->join('p.categories', 'c')
            ->where('c.id = :idC')
            ->andWhere('p.publishedAt < :date')
            ->setParameter('date', $date)
            ->setParameter('idC', $category->getId())
            ->orderBy('p.publishedAt', 'DESC')
            ->getQuery()
            ->getResult()
            ;
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
    */

    /*
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

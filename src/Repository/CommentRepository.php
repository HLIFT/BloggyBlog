<?php

namespace App\Repository;

use App\Entity\Comment;
use App\Entity\Post;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Comment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Comment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Comment[]    findAll()
 * @method Comment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comment::class);
    }

    /**
     * @return Comment[]
     */
    public function findAllRecent()
    {
        return $this->createQueryBuilder('c')
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * @return Comment[]
     */
    public function findAllRecentValid(int $max)
    {
        $date =new DateTime();

        return $this->createQueryBuilder('c')
            ->where('c.createdAt < :date')
            ->andWhere('c.valid = true')
            ->setParameter('date', $date)
            ->orderBy('c.createdAt', 'DESC')
            ->setMaxResults($max)
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * @return Comment[]
     */
    public function findPostRecent(Post $post)
    {
        return $this->createQueryBuilder('c')
            ->orderBy('c.createdAt', 'DESC')
            ->andWhere('c.post = :post')
            ->setParameter('post', $post)
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * @return Comment[]
     */
    public function findCommentRecent(int $max)
    {
        return $this->createQueryBuilder('c')
            ->orderBy('c.createdAt', 'DESC')
            ->setMaxResults($max)
            ->getQuery()
            ->getResult()
            ;
    }

    // /**
    //  * @return Comment[] Returns an array of Comment objects
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
    public function findOneBySomeField($value): ?Comment
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

<?php

namespace App\Repository;

use App\Entity\TaskList;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TaskList|null find($id, $lockMode = null, $lockVersion = null)
 * @method TaskList|null findOneBy(array $criteria, array $orderBy = null)
 * @method TaskList[]    findAll()
 * @method TaskList[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TaskListRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TaskList::class);
    }

    public function findOwnerlessLists($deleted = false)
    {
        $query = $this->createQueryBuilder('l')
            ->andWhere('l.owner IS NULL')
        ->orderBy('l.sort','ASC');

        if (!$deleted) {
            $query->andWhere('l.deletedAt IS NULL');
        }

        return $query->getQuery()->getResult();
    }

    /**
     * @param User|null $user
     * @param int $id
     * @param bool $deleted
     * @return int|mixed|string|null
     */
    public function findOwnersList(?User $user, int $id, $deleted = false)
    {
        $query = $this->createQueryBuilder('l')
            ->andWhere('l.id = :id')
            ->setParameter('id', $id);

        if ($user instanceof User) {
            $query->andWhere('l.owner = :owner')->setParameter('owner', $user);
        } else {
            $query->andWhere('l.owner IS NULL');
        }

        if (!$deleted) {
            $query->andWhere('l.deletedAt IS NULL');
        }

        try {
            return $query
                ->getQuery()
                ->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            return null;
        }
    }

    // /**
    //  * @return TaskList[] Returns an array of TaskList objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?TaskList
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

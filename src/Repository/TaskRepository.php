<?php

namespace App\Repository;

use App\Entity\Task;
use App\Entity\TaskList;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Task|null find($id, $lockMode = null, $lockVersion = null)
 * @method Task|null findOneBy(array $criteria, array $orderBy = null)
 * @method Task[]    findAll()
 * @method Task[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }

    /**
     * @param TaskList $taskList
     * @param bool $deleted
     * @param bool $archived
     * @return int|mixed|string
     */
    public function findAllByList(TaskList $taskList, bool $deleted = false, bool $archived = false)
    {
        $query = $this->createQueryBuilder('t')
            ->andWhere('t.taskList = :taskList')
            ->setParameter('taskList', $taskList);


        if (!$deleted) {
            $query->andWhere('t.deletedAt IS NULL');
        }
        if (!$archived) {
            $query->andWhere('t.archivedAt IS NULL');
        }

        return $query->getQuery()->getResult();
    }

    public function findByList(TaskList $taskList, int $id, bool $deleted = false, bool $archived = false)
    {
        $query = $this->createQueryBuilder('t')
            ->andWhere('t.taskList = :taskList')
            ->setParameter('taskList', $taskList)
            ->andWhere('t.id = :id')
            ->setParameter('id', $id);


        if (!$deleted) {
            $query->andWhere('t.deletedAt IS NULL');
        }
        if (!$archived) {
            $query->andWhere('t.archivedAt IS NULL');
        }

        return $query->getQuery()->getResult();
    }

    // /**
    //  * @return Task[] Returns an array of Task objects
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
    public function findOneBySomeField($value): ?Task
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

<?php

namespace App\Repository;

use App\Entity\Transaction;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Transaction>
 */
class TransactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transaction::class);
    }

    public function getGroupedInventoryByUser(User $user): array
    {
        return $this->createQueryBuilder('t')
            ->join('t.item', 'i')
            ->join('t.categoria', 'c')
            ->join('t.calidad', 'q')
            ->select(
                'i.nombre AS itemNombre',
                'c.nombre AS categoriaNombre',
                'q.nombre AS calidadNombre',
                'SUM(t.cantidad) AS totalCantidad',
                'SUM(t.precio) AS totalPrecio',
                '(SUM(t.precio) / SUM(t.cantidad)) AS precioMedio'
            )
            ->where('t.user = :user')
            ->setParameter('user', $user)
            ->groupBy('i.id, c.id, q.id')
            ->getQuery()
            ->getResult();
    }


    //    /**
    //     * @return Transaction[] Returns an array of Transaction objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('t.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Transaction
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}

<?php

namespace App\Repository;

use App\Entity\Medicament;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Medicament>
 */
class MedicamentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Medicament::class);
    }


    public function findBySearchTerm(string $searchTerm, int $limit, int $offset)
    {
        return $this->createQueryBuilder('m')
            ->where('m.nom LIKE :searchTerm OR m.codeCIS LIKE :searchTerm')
            ->setParameter('searchTerm', '%' . $searchTerm . '%')
            ->orderBy('m.id')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult();
    }


    public function countBySearchTerm(string $searchTerm)
    {
        return $this->createQueryBuilder('m')
            ->select('count(m.id)')
            ->where('m.nom LIKE :searchTerm')
            ->setParameter('searchTerm', '%' . $searchTerm . '%')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findByCis(string $cis, int $limit, int $offset)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.cis = :cis')
            ->setParameter('cis', $cis)
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function countByCis(string $cis)
    {
        return $this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->andWhere('m.cis = :cis')
            ->setParameter('cis', $cis)
            ->getQuery()
            ->getSingleScalarResult();
    }


    //    /**
    //     * @return Medicament[] Returns an array of Medicament objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('m')
    //            ->andWhere('m.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('m.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Medicament
    //    {
    //        return $this->createQueryBuilder('m')
    //            ->andWhere('m.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}

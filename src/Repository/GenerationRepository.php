<?php

namespace App\Repository;

use App\Entity\Generation;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Generation>
 */
class GenerationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Generation::class);
    }

    public function countByUserOnDate(User $user, \DateTimeImmutable $startOfDay, \DateTimeImmutable $endOfDay): int
    {
        return $this->createQueryBuilder('g')
            ->select('COUNT(g.id)')
            ->where('g.user = :user')
            ->andWhere('g.createdAt BETWEEN :startOfDay AND :endOfDay')
            ->setParameter('user', $user)
            ->setParameter('startOfDay', $startOfDay)
            ->setParameter('endOfDay', $endOfDay)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /**
     * @return Generation[]
     */
    public function findByUserOrderedByDate(User $user): array
    {
        return $this->createQueryBuilder('g')
            ->leftJoin('g.tool', 't')
            ->addSelect('t')
            ->andWhere('g.user = :user')
            ->setParameter('user', $user)
            ->orderBy('g.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }
}

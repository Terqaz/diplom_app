<?php

namespace App\Repository;

use App\Entity\RespondentAttempt;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RespondentAttempt>
 *
 * @method RespondentAttempt|null find($id, $lockMode = null, $lockVersion = null)
 * @method RespondentAttempt|null findOneBy(array $criteria, array $orderBy = null)
 * @method RespondentAttempt[]    findAll()
 * @method RespondentAttempt[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RespondentAttemptRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RespondentAttempt::class);
    }

    public function save(RespondentAttempt $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(RespondentAttempt $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return RespondentAttempt[] Returns an array of RespondentAttempt objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('r.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?RespondentAttempt
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}

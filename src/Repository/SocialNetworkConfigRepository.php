<?php

namespace App\Repository;

use App\Entity\SocialNetworkConfig;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SocialNetworkConfig>
 *
 * @method SocialNetworkConfig|null find($id, $lockMode = null, $lockVersion = null)
 * @method SocialNetworkConfig|null findOneBy(array $criteria, array $orderBy = null)
 * @method SocialNetworkConfig[]    findAll()
 * @method SocialNetworkConfig[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SocialNetworkConfigRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SocialNetworkConfig::class);
    }

    public function save(SocialNetworkConfig $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(SocialNetworkConfig $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return SocialNetworkConfig[] Returns an array of SocialNetworkConfig objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?SocialNetworkConfig
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}

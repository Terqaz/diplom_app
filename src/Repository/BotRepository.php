<?php

namespace App\Repository;

use App\Entity\Bot;
use App\Entity\BotUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Bot>
 *
 * @method Bot|null find($id, $lockMode = null, $lockVersion = null)
 * @method Bot|null findOneBy(array $criteria, array $orderBy = null)
 * @method Bot[]    findAll()
 * @method Bot[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BotRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Bot::class);
    }

    public function save(Bot $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return Bot[] Returns an array of Bot objects
     */
    public function findByUserId(int $userId): array
    {
        return $this->_em->createQuery('
            SELECT b FROM App\Entity\Bot AS b
            JOIN b.botUsers AS bu
            JOIN bu.userData u WITH u.id = :userId
            WHERE bu.role IS NOT NULL
        ')
            ->setParameter('userId', $userId)
            ->getResult();
    }
}

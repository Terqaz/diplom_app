<?php

namespace App\Repository;

use App\Entity\BotUser;
use App\Entity\Survey;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Survey>
 *
 * @method Survey|null find($id, $lockMode = null, $lockVersion = null)
 * @method Survey|null findOneBy(array $criteria, array $orderBy = null)
 * @method Survey[]    findAll()
 * @method Survey[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SurveyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Survey::class);
    }

    public function save(Survey $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Survey $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByUserId(int $userId)
    {
        return $this->_em->createQuery('
            SELECT s FROM App\Entity\Survey AS s
            JOIN s.bot AS b
            JOIN b.botUsers AS bu
            JOIN bu.surveyUsers AS su
            JOIN bu.userData u WITH u.id = :userId
            WHERE bu.role IS NOT NULL
                AND su.role IS NOT NULL
        ')
            ->setParameter('userId', $userId)
            ->getResult();
    }
}

<?php

namespace App\Repository;

use App\Entity\RespondentForm;
use DateInterval;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RespondentForm>
 *
 * @method RespondentForm|null find($id, $lockMode = null, $lockVersion = null)
 * @method RespondentForm|null findOneBy(array $criteria, array $orderBy = null)
 * @method RespondentForm[]    findAll()
 * @method RespondentForm[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RespondentFormRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RespondentForm::class);
    }

    public function save(RespondentForm $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(RespondentForm $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findFormsCountsByLastDaysCount(int $surveyId, int $lastDaysCount): array
    {
        return $this->getEntityManager()->createQuery(
            'SELECT 
                f.sentDate as sentDate, 
                count(f.id) AS count 
            FROM App\Entity\RespondentForm f
                WHERE f.sentDate > :startDate
                    AND f.survey = :surveyId
            GROUP BY sentDate'
        )
            ->setParameter('startDate', (new DateTime())->sub(new DateInterval('P' . $lastDaysCount . 'D')))
            ->setParameter('surveyId', $surveyId)
            ->getArrayResult();
    }
}

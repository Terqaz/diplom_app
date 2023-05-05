<?php

namespace App\Repository;

use App\Entity\JumpCondition;
use App\Entity\Question;
use App\Entity\Respondent;
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

    /**
     * @return Survey[]
     */
    public function findByUserId(int $userId): array
    {
        return $this->_em->createQuery(
            'SELECT s FROM App\Entity\Survey AS s
                JOIN s.users AS su
                JOIN su.userData u WITH u.id = :userId'
        )
            ->setParameter('userId', $userId)
            ->getResult();
    }

    /**
     * Получить доступные для прохождения включенные опросы бота для определенного респондента
     * 
     * @return Survey[]
     */
    public function findAvailableSurveys(int $botId, int $respondentId): array
    {
        return $this->getEntityManager()->createQuery(
            'SELECT DISTINCT s
            FROM App\Entity\Survey s
                LEFT JOIN s.respondentForms rf
                JOIN s.bot b WITH b.id = :botId
                JOIN b.respondentAccesses bra 
                    WITH s.isPrivate = FALSE OR bra.respondent = :respondentId
                JOIN s.respondentAccesses sra 
                    WITH sra.respondent = :respondentId
                        AND s.isPrivate = FALSE OR sra.respondent = bra.respondent
                LEFT JOIN s.schedule sch
            WHERE s.isEnabled = TRUE
                AND (
                    ( -- Одноразовый не пройденный опрос
                        s.isMultiple = FALSE 
                        AND sch.id IS NULL
                        AND rf.id IS NULL
                    )
                    OR
                    -- Многоразовый опрос
                    s.isMultiple = TRUE
                    OR
                    -- Не пройденная итерация отложенного или регулярного опроса
                    (( -- Дата последней отправки формы респондентом
                        SELECT MAX(rf2.sentDate) FROM App\Entity\RespondentForm rf2
                        JOIN rf2.survey s3 WITH s3.id = s.id
                        WHERE rf2.respondent = rf2.respondent
                    ) < ( -- Дата начала последней итерации опроса
                        SELECT MAX(si.startDate) FROM App\Entity\SurveyIteration si
                        JOIN si.survey s2 WITH s2.id = s.id
                    ))
                )'
        )
            ->setParameter('respondentId', $respondentId)
            ->setParameter('botId', $botId)
            ->getResult();
    }

    public function findQuestionNumber(int $surveyId, int $questionFormNumber): int
    {
        return $this->getEntityManager()->createQuery(
            'SELECT COUNT(q.id) FROM App\Entity\Question q
            WHERE q.survey = :surveyId AND q.serialNumber <= :questionFormNumber'
        )
            ->setParameter('surveyId', $surveyId)
            ->setParameter('questionFormNumber', $questionFormNumber)
            ->getSingleScalarResult();
    }
}

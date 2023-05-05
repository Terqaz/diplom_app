<?php

namespace App\Repository;

use App\Entity\RespondentAnswer;
use App\Entity\Survey;
use App\Enum\QuestionType;
use App\Form\Survey\Filter\AnswerFilter;
use App\Form\Survey\Filter\RespondentFormFilter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Comparison;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\Expr\Expr as Expr;
use Doctrine\ORM\Query\Parameter;

use function Symfony\Component\DependencyInjection\Loader\Configurator\expr;

/**
 * @extends ServiceEntityRepository<RespondentAnswer>
 *
 * @method RespondentAnswer|null find($id, $lockMode = null, $lockVersion = null)
 * @method RespondentAnswer|null findOneBy(array $criteria, array $orderBy = null)
 * @method RespondentAnswer[]    findAll()
 * @method RespondentAnswer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RespondentAnswerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RespondentAnswer::class);
    }

    public function save(RespondentAnswer $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(RespondentAnswer $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function countByQuestionId(int $questionId): array
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT a.value, count(a.id) AS count FROM App\Entity\RespondentAnswer a
                JOIN a.question q WITH q.id = :questionId
                GROUP BY a.value
                ORDER BY a.value'
            )
            ->setParameter('questionId', $questionId)
            ->getArrayResult();
    }

    // private const EXPR_BY_TYPE = [
    //     AnswerFilter::NOT_NULL => 'isNotNull',
    //     AnswerFilter::NULL => 'isNull',
    //     AnswerFilter::CONTAINS => 'like',
    //     AnswerFilter::STARTS_WITH => 'like',
    //     AnswerFilter::ENDS_WITH => 'like',
    //     AnswerFilter::IN => 'in',
    //     AnswerFilter::NOT_IN => 'notIn',
    //     AnswerFilter::GT => 'gt',
    //     AnswerFilter::GTE => 'gte',
    //     AnswerFilter::LT => 'lt',
    //     AnswerFilter::LTE => 'lte',
    // ];

    // todo фильтры по значениям
    public function findByFilter(Survey $survey, RespondentFormFilter $filter): array
    {
        // Находим подходящие анкеты
        // $qb = $this->getEntityManager()->createQueryBuilder()
        //     ->select(
        //         'f.id AS formId'
        //     )
        //     ->distinct()
        //     ->from('App\Entity\Question', 'q')
        //     ->leftJoin('q.answers', 'ra')
        //     ->leftJoin('ra.form', 'f');
        // ->leftJoin('ra.answerVariant', 'av')
        // ->join('ra.respondent', 'r')
        // ->where('q.survey = :surveyId')
        // ->setParameter('surveyId', $survey->getId());

        $displayedQuestions = array_filter(
            $filter->getAnswers(),
            fn (AnswerFilter $f) => $f->isShow()
        );

        // $valueCondition = $qb->expr()->orX();

        // if ($filter->getEmail()?->isShow()) {
        //     $condition = $qb->expr()->andX(
        //         $qb->expr()->eq('r.email', ':serialNumber' . $i)
        //     );
        // }

        // foreach ($displayedQuestions as $i => $answerFilter) {
        //     $condition = $qb->expr()->andX(
        //         $qb->expr()->eq('q.serialNumber', ':serialNumber' . $i)
        //     );

        //     $type = $answerFilter->getType();
        //     if ($type !== null) {
        //         $value = trim($answerFilter->getValue());
        //         $expr = self::EXPR_BY_TYPE[$answerFilter->getType()];

        //         if (AnswerFilter::CONTAINS === $type) {
        //             $value = '%' . $value . '%';
        //         } else if (AnswerFilter::STARTS_WITH === $type) {
        //             $value = $value . '%';
        //         } else if (AnswerFilter::ENDS_WITH === $type) {
        //             $value = '%' . $value;
        //         } else if (in_array($type, [AnswerFilter::IN, AnswerFilter::NOT_IN])) {
        //             $value = array_map('trim', explode(',', $value));
        //         }

        //         if (in_array($type, [AnswerFilter::NOT_NULL, AnswerFilter::NULL])) {
        //             $comparison = $qb->expr()->orX(
        //                 $qb->expr()->$expr('ra.value'),
        //                 $qb->expr()->$expr('av.value'),
        //             );
        //         } else {
        //             $comparison = $qb->expr()->orX(
        //                 $qb->expr()->$expr('ra.value', ':value' . $i),
        //                 $qb->expr()->$expr('av.value', ':value' . $i),
        //             );
        //         }

        //         $condition->add($comparison);
        //     }

        //     $valueCondition->add($condition);

        //     $qb->setParameter('serialNumber' . $i, $answerFilter->getQuestionFormNumber());

        //     if (null !== $type && !in_array($type, [AnswerFilter::NOT_NULL, AnswerFilter::NULL])) {
        //         $qb->setParameter('value' . $i, $value);
        //     }
        // }

        // if ($valueCondition->count() > 0) {
            // $qb
            //     ->leftJoin('ra.answerVariant', 'av', 'WITH', $valueCondition)
            //     // ->join('ra.respondent', 'r')
            //     ->where('q.survey = :surveyId')
            //     ->setParameter('surveyId', $survey->getId());
            // // ->andWhere($valueCondition);

            // // dd($qb->getDQL(), $qb->getParameters());
            // $forms = $qb->getQuery()->getArrayResult();
            // dd($forms);

            // Получаем ответы из подходящих анкет
            $qb2 = $this->getEntityManager()->createQueryBuilder()
                ->select(
                    'f.id AS formId',
                );

            if ($filter->getEmail()?->isShow()) {
                $qb2->addSelect('r.email AS email');
            }

            $qb2->addSelect(
                'q.serialNumber AS questionNumber',
                '(CASE WHEN q.type IN (:orderedAnswersQuestionTypes) THEN true ELSE false END) AS isAnswersOrdered',
                'ra.serialNumber AS answerNumber',
                'ra.value AS ownAnswer',
                'av.value AS selectedAnswer',
            )
                ->distinct()
                ->from('App\Entity\Question', 'q')
                ->leftJoin('q.answers', 'ra')
                ->join('ra.form', 'f')
                // ->join('ra.form', 'f', 'WITH', 'f.id IN (:formIds)')
                ->leftJoin('ra.answerVariant', 'av')
                ->join('ra.respondent', 'r');

            // Нужно только условие по номерам отображаемых вопросов
            $questionFormNumbers = [];
            foreach ($displayedQuestions as $displayedQuestion) {
                $questionFormNumbers[] = $displayedQuestion->getQuestionFormNumber();
            }

            $qb2
                ->where('q.survey = :surveyId')
                ->setParameter('surveyId', $survey->getId())
                ->andWhere('q.serialNumber IN (:questionFormNumbers)')
                ->setParameter('questionFormNumbers', $questionFormNumbers)
                ->setParameter(':orderedAnswersQuestionTypes', [
                    QuestionType::CHOOSE_ORDERED,
                    QuestionType::CHOOSE_ALL_ORDERED,
                ]);
                // ->setParameter('formIds', array_column($forms, 'formId'));

            // dd($qb2->getDQL(), $qb2->getParameters());

            $formsAnswers = $qb2->getQuery()
                ->getArrayResult();
        // } else {
        //     $formsAnswers = [];
        // }

        // dd($formsAnswers);

        /**
         * Ответы по id формы, serialNumber вопроса и serialNumber ответа
         * @var array<int, array<int, array<int, string>>>
         */
        $formsTable = [];

        // $columnsCount = count($displayedQuestions);

        foreach ($formsAnswers as $formsAnswer) {
            [
                'formId' => $formId,
                'questionNumber' => $questionNumber,
                'isAnswersOrdered' => $isAnswersOrdered,
                'answerNumber' => $answerNumber,
                'ownAnswer' => $ownAnswer,
                'selectedAnswer' => $selectedAnswer
            ] = $formsAnswer;

            $email = $formsAnswer['email'] ?? null;
            if ($email !== null) {
                $formsTable[$formId]['email'] = $email;
            }

            if (null !== $ownAnswer) {
                $answerValue = $ownAnswer;
            } else if (null !== $selectedAnswer) {
                $answerValue = $selectedAnswer;
            } else {
                continue;
            }

            $formsTable[$formId][$questionNumber]['values'][$answerNumber] = $answerValue;
            $formsTable[$formId][$questionNumber]['isOrdered'] = $isAnswersOrdered;
        }

        // dd($formsTable);

        return $formsTable;
    }
}

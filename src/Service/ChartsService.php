<?php

namespace App\Service;

use App\Entity\Question;
use App\Entity\RespondentAnswer;
use App\Entity\RespondentForm;
use App\Entity\Survey;
use App\Repository\RespondentAnswerRepository;
use App\Repository\RespondentFormRepository;
use App\Repository\SurveyRepository;
use DateInterval;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

class ChartsService
{
    private const TRANSLATION_PATH = 'forms.respondent_forms_chart.';

    public const PERIOD_NAMES = [
        7 => self::TRANSLATION_PATH . 'during_last_week',
        30 => self::TRANSLATION_PATH . 'during_last_month',
        180 => self::TRANSLATION_PATH . 'during_last_6_months',
        365 => self::TRANSLATION_PATH . 'during_last_year',
    ]; 

    private const DATE_FORMAT = 'Y-m-d'; 

    private ChartBuilderInterface $chartBuilder;
    private EntityManagerInterface $em;
    private TranslatorInterface $translator;

    public function __construct(ChartBuilderInterface $chartBuilder, EntityManagerInterface $em, TranslatorInterface $translator)
    {
        $this->chartBuilder = $chartBuilder;
        $this->em = $em;
        $this->translator = $translator;
    }

    public function createFormsByTimeChart(Survey $survey, int $lastDaysCount): Chart
    {
        $labels = [];
        $values = [];

        $startDate = (new DateTime())
            ->sub(new DateInterval('P' . $lastDaysCount . 'D'));

        /** @var ArrayCollection $forms */
        $forms = $survey->getRespondentForms();
        $forms = $forms->matching(Criteria::create()->where(
            Criteria::expr()
                ->gte('sentDate', $startDate) // ->format(self::DATE_FORMAT)
        ));

        $formsByDate = [];

        /** @var RespondentForm $form */
        foreach ($forms as $form) {
            $label = $form->getSentDate()->format(self::DATE_FORMAT);

            $oldValue = $formsByDate[$label] ?? 0;
            $formsByDate[$label] = $oldValue + 1;
        }

        for ($i=0; $i < $lastDaysCount; $i++) {
            $label = $startDate->add(new DateInterval('P1D'))->format(self::DATE_FORMAT);

            $labels[] = $label;
            $values[] = $formsByDate[$label] ?? 0;
        }

        return $this->chartBuilder->createChart(Chart::TYPE_LINE)
            ->setData([
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => $this->translator->trans(self::PERIOD_NAMES[$lastDaysCount]),
                        'backgroundColor' => 'rgb(255, 99, 132)',
                        'borderColor' => 'rgb(255, 99, 132)',
                        'data' => $values,
                        'tension' => 0.2
                    ],
                ],
            ])->setOptions([
                'scales' => [
                    'y' => [
                        'suggestedMin' => 0,
                        'ticks' => [
                            'stepSize' => 1
                        ]
                    ],
                ],
            ]);
    }

    public function createAnswersCountChart(Question $question): ?Chart
    {
        $labels = [];
        $values = [];

        /** @var RespondentAnswerRepository */
        $respondentAnswerRepository = $this->em->getRepository(RespondentAnswer::class);

        // Для вопросов без вариантов ответов не строим графики, но выводим, что там нет вариантов
        if (0 === $question->getVariants()->count()) {
            if (null === $question->getNumberVariantsBorders()) {
                return null;
            }

            $answersCounts = $respondentAnswerRepository->countByQuestionId($question->getId());
            foreach ($answersCounts as ['value' => $value, 'count' => $count]) {
                $labels[] = $value;
                $values[] = (int) $count;
            }
        } else {
            foreach ($question->getVariants() as $variant) {
                $labels[] = $variant->getValue();
                $values[] = $variant->getRespondentAnswers()->count();
            }
        }

        return $this->chartBuilder->createChart(Chart::TYPE_BAR)
            ->setData([
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => $question->getTitle(),
                        'backgroundColor' => 'rgb(255, 99, 132)',
                        'data' => $values,
                        'tension' => 0.2
                    ],
                ]
            ])
            ->setOptions([
                'indexAxis' => 'y',

                'categoryPercentage' => 1.0,
                'barPercentage' => 1.0,
                'barThickness' => 20,

                'borderWidth' => 0,

                'scales' => [
                    'x' => [
                        'beginAtZero' => true,
                        'ticks' => [
                            'stepSize' => 1
                        ]
                    ]
                ]
            ]);
    }
}

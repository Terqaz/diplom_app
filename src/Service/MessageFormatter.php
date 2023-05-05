<?php

namespace App\Service;

use App\Entity\AnswerVariant;
use App\Entity\Question;
use App\Enum\QuestionType;
use Doctrine\ORM\EntityManagerInterface;

class MessageFormatter
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Форматирует список всех вопросов анкеты
     *
     * @param iterable $questions - отсортированные по номеру вопросы
     * @return string
     */
    public function formatQuestions(iterable $questions): string
    {
        $message = ['Все вопросы анкеты (при заполнении анкеты некоторые вопросы могут быть автоматически пропущены):'];

        $i = 1;
        foreach ($questions as $question) {
            $message[] = $this->formatQuestionTitle($i++, $question->getTitle());
        }

        return implode("\n\n", $message);
    }

    public function formatFullQuestion(Question $question, int $questionNumber): string
    {
        $message = $this->formatQuestionTitle($questionNumber, $question->getTitle());

        $help = [];

        if (!$question->isRequired()) {
            $help[] = 'необязательный';
        }

        if ($question->getType() === QuestionType::CHOOSE_ONE) {
            $help[] = 'один ответ';
        } else if (in_array($question->getType(), [QuestionType::CHOOSE_MANY, QuestionType::CHOOSE_ORDERED])) {
            $manyVariantsHelp = [];

            if ($question->getMaxVariants() === 1) {
                $manyVariantsHelp[] = 'один выбираемый ответ';
            } else if ($question->getMaxVariants() >= 2) {
                $manyVariantsHelp[] = 'до ' . $question->getMaxVariants() . ' выбираемых ответов';
            }

            if ($question->getOwnAnswersCount() === 1) {
                $manyVariantsHelp[] = 'один ваш ответ';
            } else if ($question->getOwnAnswersCount() >= 2) {
                $manyVariantsHelp[] = 'до ' . $question->getOwnAnswersCount() . ' ваших ответов';
            }

            $help[] = implode(' и/или ', $manyVariantsHelp);
        } else if ($question->getType() === QuestionType::CHOOSE_ALL_ORDERED) {
            $help[] = 'расположите все ответы по порядку';
        }

        if ($question->getIntervalBorders() !== null) {
            [$left, $right] = $question->getNumberVariantsBorders();
            $help[] = 'число от ' . $left . ' до ' . $right;
        }

        $message .= ' (' . implode(', ', $help) . ')';

        $variants = $question->getVariants()->toArray();

        if (count($variants) > 0) {
            $i = 1;
            $formattedVariants = [];
            foreach ($question->getVariants()->toArray() as $v) {
                $formattedVariants[] = $i++ . '. ' . $v->getValue();
            }

            $message .= "\n\n" . implode("\n", $formattedVariants);
        }

        return $message;
    }

    protected function formatQuestionTitle(int $number, string $title): string
    {
        return 'Вопрос №' . $number . '. ' . $title;
    }

    public function formatAnswers(int $questionNumber, array $answers): string
    {
        $message = ['Ваши ответы на вопрос №' . $questionNumber . ':'];

        foreach ($answers as $i => [$isChoosed, $answer]) {
            if ($isChoosed) {
                $message[] = ($i + 1) . '. ' . $this->em->find(AnswerVariant::class, $answer)->getValue();
            } else {
                $message[] = ($i + 1) . '. ' . $answer;
            }
        }

        return implode("\n", $message);
    }
}

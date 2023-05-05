<?php 

namespace App\Dto;

use App\Entity\Respondent;
use Symfony\Component\Serializer\Annotation\Ignore;

/**
 * Кешируемые данные для респондента
 */
class RespondentStateData
{
    /** Название состояния */
    private string $state;

    private Respondent $respondent;

    /**
     * id доступных опросов по их отображенному номеру 
     *
     * @var array<int, int>
     */
    private array $availableSurveys = [];

    private ?int $surveyId = null;

    private ?int $questionId = null;

    /** Номер вопроса */
    private ?int $questionNumber = null;
    private ?int $formElementNumber = null;
    private ?int $questionMessageId = null;
    private ?int $actionHelpMessageId = null;

    /** Нужен, чтобы редактировать сообщение в ВК */
    private ?string $questionMessageText = null;
    
    private bool $isNextQuestionNotified = false;

    private int $answerNumber = 0;

    /**
     * Id выбранных ответов и значения собственных ответов по id вопроса
     * @var array<int, array{choosed: int[], own: string[]}>|null
     */
    private ?array $answersByQuestion = [];

    public function __construct(string $state) {
        $this->state = $state;
    }

    public function toNextQuestion(?int $newQuestionId, ?int $questionNumber = null, ?int $nextFormElementNumber = null): self
    {
        $this->questionId = $newQuestionId;
        $this->questionNumber = $questionNumber;
        $this->formElementNumber = $nextFormElementNumber;
        
        $this->questionMessageId = null;
        $this->questionMessageText = null;

        $this->answerNumber = 0;

        return $this;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function setState(string $state): self
    {
        $this->state = $state;

        return $this;
    }

    #[Ignore]
    public function getRespondent(): Respondent
    {
        return $this->respondent;
    }

    #[Ignore]
    public function setRespondent(Respondent $respondent): self
    {
        $this->respondent = $respondent;

        return $this;
    }

    public function getAvailableSurveys(): array
    {
        return $this->availableSurveys;
    }

    public function setAvailableSurveys(array $availableSurveys): self
    {
        $this->availableSurveys = $availableSurveys;

        return $this;
    }

    public function getSurveyId(): ?int
    {
        return $this->surveyId;
    }

    public function setSurveyId(?int $surveyId): self
    {
        $this->surveyId = $surveyId;

        return $this;
    }

    public function getQuestionId(): ?int
    {
        return $this->questionId;
    }

    public function setQuestionId(?int $questionId): self
    {
        $this->questionId = $questionId;

        return $this;
    }

    public function getQuestionNumber(): ?int
    {
        return $this->questionNumber;
    }

    public function setQuestionNumber(?int $questionNumber): self
    {
        $this->questionNumber = $questionNumber;

        return $this;
    }

    public function getFormElementNumber(): ?int
    {
        return $this->formElementNumber;
    }

    public function setFormElementNumber(?int $formElementNumber): self
    {
        $this->formElementNumber = $formElementNumber;

        return $this;
    }

    public function getAnswerNumber(): int
    {
        return $this->answerNumber;
    }

    public function setAnswerNumber(int $answerNumber): self
    {
        $this->answerNumber = $answerNumber;

        return $this;
    }

    public function getAnswersByQuestion(): ?array
    {
        return $this->answersByQuestion;
    }

    public function setAnswersByQuestion(?array $answersByQuestion): self
    {
        $this->answersByQuestion = $answersByQuestion;

        return $this;
    }

    #[Ignore]
    public function getChoosedAnswers(): array
    {
        $choosedAnswers = [];

        foreach ($this->answersByQuestion as $questionNumber => $answers) {
            $choosedAnswers[$questionNumber] = $answers['choosed'];
        }

        return $choosedAnswers;
    }

    public function canGiveAnyAnswer(int $maxVariantsCount, int $maxOwnAnswersCount): bool
    {
        return $this->canChooseAnswer($maxVariantsCount)
            || $this->canGiveOwnAnswer($maxOwnAnswersCount);
    }

    public function addChoosedAnswer(int $variantNumber): self
    {
        if (!isset($this->answersByQuestion[$this->questionId])) {
            $this->answersByQuestion[$this->questionId] = 
                ['choosed' => [$this->answerNumber => $variantNumber], 'own' => []];
        } else {
            $this->answersByQuestion[$this->questionId]['choosed'][$this->answerNumber] = $variantNumber;
        }

        ++$this->answerNumber;

        return $this;
    }

    public function canChooseAnswer(int $maxVariantsCount): bool
    {
        return $maxVariantsCount > count($this->answersByQuestion[$this->questionId]['choosed'] ?? []);
    }

    public function addOwnAnswer(string $value): self
    {
        if (!isset($this->answersByQuestion[$this->questionId])) {
            $this->answersByQuestion[$this->questionId] = 
                ['choosed' => [], 'own' => [$this->answerNumber => $value]];
        } else {
            $this->answersByQuestion[$this->questionId]['own'][$this->answerNumber] = $value;
        }

        ++$this->answerNumber;

        return $this;
    }

    public function canGiveOwnAnswer(int $maxOwnAnswersCount): bool
    {
        return $maxOwnAnswersCount > count($this->answersByQuestion[$this->questionId]['own'] ?? []);
    }

    public function getQuestionMessageId(): ?int
    {
        return $this->questionMessageId;
    }

    public function setQuestionMessageId(?int $questionMessageId): self
    {
        $this->questionMessageId = $questionMessageId;

        return $this;
    }

    public function getActionHelpMessageId(): ?int
    {
        return $this->actionHelpMessageId;
    }

    public function setActionHelpMessageId(?int $actionHelpMessageId): self
    {
        $this->actionHelpMessageId = $actionHelpMessageId;

        return $this;
    }

    public function isNextQuestionNotified(): bool
    {
        return $this->isNextQuestionNotified;
    }

    public function setNextQuestionNotified(bool $isNextQuestionNotified): self
    {
        $this->isNextQuestionNotified = $isNextQuestionNotified;

        return $this;
    }

    public function getQuestionMessageText(): ?string
    {
        return $this->questionMessageText;
    }

    public function setQuestionMessageText(?string $questionMessageText): self
    {
        $this->questionMessageText = $questionMessageText;

        return $this;
    }
}
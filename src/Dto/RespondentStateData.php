<?php 

namespace App\Dto;

/**
 * Кешируемые данные для респондента
 */
class RespondentStateData
{
    /** Название состояния */
    private string $state;

    private ?int $surveyId;
    private ?int $questionId;

    public function __construct(string $state) {
        $this->state = $state;
    }

    public function getState(): string
    {
        return $this->state;
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
}
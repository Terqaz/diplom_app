<?php

namespace App\Form\Survey\Filter;

class AnswerFilter
{
    public const NOT_NULL = 'not_null';
    public const NULL = 'null';
    public const CONTAINS = 'contains';
    public const STARTS_WITH = 'starts_with';
    public const ENDS_WITH = 'ends_with';
    public const IN = 'in';
    public const NOT_IN = 'not_in';
    public const GT = 'gt';
    public const GTE = 'gte';
    public const LT = 'lt';
    public const LTE = 'lte';

    private ?int $questionNumber = null;
    private ?int $questionFormNumber = null;
    private bool $isShow = true;
    private ?string $type = null;
    private ?string $value = null;

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(?string $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function isShow(): bool
    {
        return $this->isShow;
    }

    public function setIsShow(bool $show): self
    {
        $this->isShow = $show;

        return $this;
    }

    public function getQuestionNumber(): ?int
    {
        return $this->questionNumber;
    }

    public function setQuestionNumber(int $questionNumber): self
    {
        $this->questionNumber = $questionNumber;

        return $this;
    }

    public function getQuestionFormNumber(): ?int
    {
        return $this->questionFormNumber;
    }

    public function setQuestionFormNumber(?int $questionFormNumber): self
    {
        $this->questionFormNumber = $questionFormNumber;

        return $this;
    }
}

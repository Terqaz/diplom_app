<?php

namespace App\Form\Survey\Filter;

use App\Entity\Survey;

class RespondentFormFilter
{
    public const PDF = 'pdf';
    public const JSON = 'json';
    public const YAML = 'yaml';
    public const CSV = 'csv';

    private int $count = 10;
    private bool $enableCoding = false;

    private ?AnswerFilter $phone = null;
    private ?AnswerFilter $email = null;

    /**
     * @var AnswerFilter[]
     */
    private array $answers = [];

    private string $fileFormat = self::PDF;

    public function __construct(Survey $survey)
    {
        if ($survey->isPhoneRequired()) {
            $this->phone = new AnswerFilter();
        }

        if ($survey->isEmailRequired()) {
            $this->email = new AnswerFilter();
        }

        $i = 0;
        foreach ($survey->getQuestions() as $question) {
            $this->answers[] = (new AnswerFilter())
                ->setQuestionNumber($i++)
                ->setQuestionFormNumber($question->getSerialNumber());
        }
    }

    public function isEnableCoding(): bool
    {
        return $this->enableCoding;
    }

    public function setEnableCoding(bool $enableCoding): self
    {
        $this->enableCoding = $enableCoding;

        return $this;
    }

    /**
     * @return AnswerFilter[]
     */
    public function getAnswers(): array
    {
        return $this->answers;
    }

    public function setAnswers(array $answers): self
    {
        $this->answers = $answers;

        return $this;
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function setCount(int $count): self
    {
        $this->count = $count;

        return $this;
    }

    public function getPhone(): ?AnswerFilter
    {
        return $this->phone;
    }

    public function setPhone(?AnswerFilter $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getEmail(): ?AnswerFilter
    {
        return $this->email;
    }

    public function setEmail(?AnswerFilter $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getFileFormat(): string
    {
        return $this->fileFormat;
    }

    public function setFileFormat(string $fileFormat): self
    {
        $this->fileFormat = $fileFormat;

        return $this;
    }
}

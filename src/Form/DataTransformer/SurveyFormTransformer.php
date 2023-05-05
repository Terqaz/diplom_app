<?php

namespace App\Form\DataTransformer;

use App\Entity\JumpCondition;
use App\Entity\Question;
use App\Entity\Subcondition;
use App\Entity\Survey;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Serializer\Context\Encoder\JsonEncoderContextBuilder;
use Symfony\Component\Serializer\Context\Normalizer\ObjectNormalizerContextBuilder;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\Serializer\Encoder\EncoderInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class SurveyFormTransformer implements DataTransformerInterface
{
    private NormalizerInterface $normalizer;
    private DenormalizerInterface $denormalizer;
    private DecoderInterface $decoder;

    public function __construct(
        NormalizerInterface $normalizer,
        DenormalizerInterface $denormalizer,
        DecoderInterface $decoder,
    ) {
        $this->normalizer = $normalizer;
        $this->denormalizer = $denormalizer;
        $this->decoder = $decoder;
    }

    /**
     * Опрос в array
     *
     * @param Survey $survey
     * @return array
     */
    public function transform($survey): array
    {
        $context = (new ObjectNormalizerContextBuilder())
            ->withGroups(['surveyFormEdit'])
            ->withSkipNullValues(true)
            ->toArray();

        $formElements = [];

        foreach ($survey->getQuestions() as $question) {
            $questionData = $this->normalizer->normalize($question, null, $context);

            $variantsData = [];
            foreach ($question->getVariants() as $variant) {
                $variantsData[] = $variant->getValue();
            }
            $questionData['elementType'] = 'question';
            $questionData['answerVariants'] = $variantsData;

            $formElements[$question->getSerialNumber()] = $questionData;
        }

        foreach ($survey->getJumpConditions() as $jumpCondition) {
            $subconditionsData = [];
            foreach ($jumpCondition->getSubconditions() as $subcondition) {
                $subconditionsData[] = [
                    'questionNumber' => $subcondition->getAnswerVariant()->getQuestion()->getSerialNumber(),
                    'variantNumber' => $subcondition->getAnswerVariant()->getSerialNumber(),
                    'isEqual' => $subcondition->isEqual()
                ];
            }

            $i = 1;
            foreach ($survey->getQuestions() as $question) {
                if ($jumpCondition->getToQuestion()->getSerialNumber() === $question->getSerialNumber()) {
                    break;
                }
                $i++;
            }

            $formElements[$jumpCondition->getSerialNumber()] = [
                'elementType' => 'jump',
                'toQuestion' => $i,
                'subconditions' => $subconditionsData
            ];
        }

        ksort($formElements);
        $formElements = array_values($formElements); // убираем ключи

        $surveyData = $this->normalizer->normalize($survey, null, $context);
        $surveyData['formElements'] = $formElements;

        return $surveyData;
    }

    /**
     * json в Опрос
     * 
     * @param string $json
     * @return Survey
     * 
     * @throws TransformationFailedException if object (issue) is not found.
     */
    public function reverseTransform($json): Survey
    {
        $surveyData = $this->decoder->decode($json, 'json');

        $survey = (new Survey())
            ->setIsEmailRequired($surveyData['isEmailRequired'])
            ->setIsPhoneRequired($surveyData['isPhoneRequired']);

        foreach ($surveyData['questions'] as $questionData) {
            $question = $this->denormalizer->denormalize(
                $questionData,
                Question::class,
                'array'
            );

            $survey->addQuestion($question);
        }

        foreach ($surveyData['jumpConditions'] as $jumpData) {
            $toQuestion = $survey->getQuestions()->get($jumpData['toQuestion'] - 1);

            $jump = (new JumpCondition())
                ->setSerialNumber($jumpData['serialNumber'])
                ->setToQuestion($toQuestion);

            $serialNumber = 1;
            foreach ($jumpData['subconditions'] as $subconditionData) {
                $fromQuestion = $survey->getQuestionByNumber($subconditionData['questionNumber']);

                $subcondition = (new Subcondition())
                    ->setSerialNumber($serialNumber++)
                    ->setIsEqual($subconditionData['isEqual'])
                    ->setAnswerVariant($fromQuestion->getVariantByNumber($subconditionData['variantNumber']));

                $jump->addSubcondition($subcondition);
            }

            $survey->addJumpCondition($jump);
        }

        return $survey;
    }
}

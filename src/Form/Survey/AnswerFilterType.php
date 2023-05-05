<?php

namespace App\Form\Survey;

use App\Form\Survey\Filter\AnswerFilter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AnswerFilterType extends AbstractType
{
    private const TRANS_PATH = 'forms.answer_filter';

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('questionNumber', HiddenType::class)
            ->add('questionFormNumber', HiddenType::class)
            ->add('isShow', CheckboxType::class, [
                'label' => false,
                'required' => false
            ])
            // ->add('type', ChoiceType::class, [
            //     'label' => false,
            //     'required' => false,
            //     'choices' => [
            //         self::TRANS_PATH . '.group.value' => self::getChoices(
            //             self::TRANS_PATH . '.type.value',
            //             [
            //                 AnswerFilter::NOT_NULL,
            //                 AnswerFilter::NULL,
            //                 AnswerFilter::CONTAINS,
            //                 AnswerFilter::STARTS_WITH,
            //                 AnswerFilter::ENDS_WITH,
            //                 AnswerFilter::IN,
            //                 AnswerFilter::NOT_IN,
            //             ]
            //         ),
            //         self::TRANS_PATH . '.group.comparison' => self::getChoices(
            //             self::TRANS_PATH . '.type.comparison',
            //             [
            //                 AnswerFilter::GT,
            //                 AnswerFilter::GTE,
            //                 AnswerFilter::LT,
            //                 AnswerFilter::LTE,
            //             ]
            //         ),
            //     ],
            // ])
            // ->add('value', TextType::class, [
            //     'label' => false,
            //     'required' => false,
            // ])
            ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => AnswerFilter::class
        ]);
    }

    private static function getChoices(string $transSubPath, array $values): array
    {
        $transSubPath .= '.';

        $choices = [];

        foreach ($values as $value) {
            $choices[$transSubPath . $value] = $value;
        }

        return $choices;
    }
}

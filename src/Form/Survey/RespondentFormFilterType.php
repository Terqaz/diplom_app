<?php

namespace App\Form\Survey;

use App\Form\Survey\Filter\RespondentFormFilter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RespondentFormFilterType extends AbstractType
{
    // todo фильтр по дате отправки
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('count', ChoiceType::class, [
                'choices' => [
                    10 => 10,
                    20 => 20,
                    50 => 50,
                    100 => 100,
                    'Все' => 0
                ],
            ])
            // ->add('enableCoding', CheckboxType::class, [
            //     'required' => false,
            //     'label' => 'Включить кодирование',
            // ])
            ->add('answers', CollectionType::class, [
                'label' => false,
                'entry_type' => AnswerFilterType::class,
                // 'entry_options' => ['label' => false],
            ])
            ->add('fileFormat', ChoiceType::class, [
                'label' => false,
                'choices' => [
                    RespondentFormFilter::PDF => RespondentFormFilter::PDF,
                    RespondentFormFilter::JSON => RespondentFormFilter::JSON,
                    RespondentFormFilter::YAML => RespondentFormFilter::YAML,
                    // RespondentFormFilter::CSV => RespondentFormFilter::CSV,
                ],
            ])
            ->add('updateForms', SubmitType::class, [
                'label' => 'Применить'
            ])
            ->add('loadFile', SubmitType::class, [
                'label' => 'Скачать'
            ]);

        if ($options['is_phone_required']) {
            $builder->add('phone', AnswerFilterType::class);
        }

        if ($options['is_email_required']) {
            $builder->add('email', AnswerFilterType::class);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class' => RespondentFormFilter::class,
                'csrf_protection' => false,
                'is_phone_required' => false,
                'is_email_required' => false,
                'fileFormat' => RespondentFormFilter::PDF
            ])
            ->setAllowedTypes('is_phone_required', 'bool')
            ->setAllowedTypes('is_email_required', 'bool');
    }
}

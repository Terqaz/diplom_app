<?php

namespace App\Form\RespondentAnswer;

use App\Service\ChartsService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Range;

class ChartFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('rfPeriod', ChoiceType::class, [
                'choices' => array_flip(ChartsService::PERIOD_NAMES),
                'data' => $options['rfPeriod']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'rfPeriod' => 30
        ]);

        $resolver->setAllowedTypes('rfPeriod', 'int');
    }
}

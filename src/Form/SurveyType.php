<?php

namespace App\Form;

use App\Entity\Bot;
use App\Entity\Survey;
use App\Repository\BotRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

class SurveyType extends AbstractType
{
    const INPUT_CORRECT_VALUE = 'Введите корректное значение';

    private BotRepository $botRepository;

    public function __construct(BotRepository $botRepository)
    {
        $this->botRepository = $botRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'required' => true,
                'label' => 'Название',
                'help' => 'От 10 до 255 символов',
                'constraints' => [
                    new Length(
                        min: 10,
                        max: 255,
                        minMessage: self::INPUT_CORRECT_VALUE,
                        maxMessage: self::INPUT_CORRECT_VALUE,
                    )
                ],
            ])
            ->add('description', TextType::class, [
                'required' => false,
                'label' => 'Описание',
                'help' => 'Опционально. От 16 до 1024 символов',
                'constraints' => [
                    new Length(
                        min: 16,
                        max: 1024,
                        minMessage: self::INPUT_CORRECT_VALUE,
                        maxMessage: self::INPUT_CORRECT_VALUE,
                    )
                ],
            ])
            ->add('isPrivate', CheckboxType::class, [
                'required' => false,
                'label' => 'Приватный',
                'help' => 'forms.common.is_private.help',
            ])
            ->add('isEnabled', CheckboxType::class, [
                'required' => false,
                'label' => 'Активен',
                'help' => 'Активные опросы доступны для прохождения в боте'
            ]);

        if ($options['is_new']) {
            $typeOptions = [
                'label' => 'Бот',
                'class' => Bot::class,
                'choices' => $this->botRepository->findByUserId($options['user_id']),
                'choice_label' => 'title',
            ];

            if ($options['bot'] !== null) {
                $typeOptions['data'] = $options['bot'];
            }

            $builder
                ->add('bot', EntityType::class, $typeOptions);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Survey::class,
            'bot' => null
        ]);

        $resolver->setRequired(['is_new']);
        $resolver->setRequired(['user_id']);

        $resolver->setAllowedTypes('user_id', 'int');
        $resolver->setAllowedTypes('bot', [Bot::class, 'null']);
        $resolver->setAllowedTypes('is_new', 'bool');
    }
}

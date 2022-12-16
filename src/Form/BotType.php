<?php

namespace App\Form;

use App\Entity\Bot;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;

class BotType extends AbstractType
{
    const INPUT_CORRECT_VALUE = 'Введите корректное значение';
    const IS_PRIVATE_HELP = 'Пользователи, не имеющие доступа к приватному боту не смогут найти его в поиске и просматривать информацию о нем';

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'required' => true,
                'label' => 'Название',
                'help' => 'От 10 до 255 символов',
                'constraints' => [
                    new Length(min: 10, max: 255,
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
                    new Length(min: 16, max: 1024,
                        minMessage: self::INPUT_CORRECT_VALUE,
                        maxMessage: self::INPUT_CORRECT_VALUE,
                    )
                ],
            ])
            ->add('isPrivate', CheckboxType::class, [
                'label' => 'Приватный',
                'help' => self::IS_PRIVATE_HELP,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Bot::class,
        ]);
    }
}

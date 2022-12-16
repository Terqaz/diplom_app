<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class RegistrationFormType extends AbstractType
{
    const INPUT_CORRECT_VALUE = 'Введите корректное значение';

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('lastName', TextType::class, [
                'label' => 'Фамилия',
                'required' => true,
                'help' => 'От 1 до 64 букв',
                'constraints' => [
                    new Length(max: 64, maxMessage: self::INPUT_CORRECT_VALUE),
                    new Regex('/^[А-ЯЁ][а-яё]+(-[А-ЯЁ])?[а-яё]+$/u', self::INPUT_CORRECT_VALUE)
                ],
            ])
            ->add('firstName', TextType::class, [
                'label' => 'Имя',
                'required' => true,
                'help' => 'От 1 до 64 букв',
                'constraints' => [
                    new Length(max: 64, maxMessage: self::INPUT_CORRECT_VALUE),
                ],
            ])
            ->add('patronymic', TextType::class, [
                'label' => 'Отчество',
                'required' => false,
                'help' => 'От 0 до 64 букв',
                'constraints' => [
                    new Length(max: 64, maxMessage: self::INPUT_CORRECT_VALUE),
                ],
            ])
            ->add('email', EmailType::class, [
                'required' => true,
                'help' => 'До 180 символов',
            ])
            ->add('phone', TelType::class, [
                'required' => false,
                'label' => 'Номер телефона',
                'help' => 'Опционально'
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'label' => 'Согласен с условиями пользования сервисом',
                'constraints' => [
                    new IsTrue([
                        'message' => 'Вы должны согласиться с условиями пользования',
                    ]),
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,

                'attr' => ['autocomplete' => 'new-password'],
                'label' => 'Пароль',
                'constraints' => [
                    new NotBlank([
                        'message' => self::INPUT_CORRECT_VALUE,
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Ваш пароль должен состоять минимум из {{ limit }} симоволов',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}

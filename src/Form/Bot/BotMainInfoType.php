<?php

namespace App\Form\Bot;

use App\Entity\Bot;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;

class BotMainInfoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'required' => true,
                'label' => 'Название',
                'help' => 'От 10 до 255 символов',
            ])
            ->add('description', TextType::class, [
                'required' => false,
                'label' => 'Описание',
                'help' => 'Опционально. От 16 до 1024 символов',
            ])
            ->add('isPrivate', CheckboxType::class, [
                'required' => false,
                'label' => 'Приватный',
                'help' => 'forms.common.is_private.help',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Bot::class,
        ]);
    }
}

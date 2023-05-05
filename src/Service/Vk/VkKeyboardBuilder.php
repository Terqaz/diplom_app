<?php

namespace App\Service\Vk;

use InvalidArgumentException;
use Symfony\Component\Serializer\Encoder\EncoderInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

class VkKeyboardBuilder
{
    public const TEXT_BUTTON = 'text';

    private ?array $buttons;

    public function __construct()
    {
    }

    public static function newTextButton(string $label): array
    {
        return [
            'action' => [
                'type' => self::TEXT_BUTTON,
                'label' => $label
            ]
        ];
    }

    public function buttons(array $buttons): self
    {
        $this->buttons = $buttons;

        return $this;
    }

    public function build(): string
    {
        if (null === $this->buttons) {
            throw new InvalidArgumentException("Buttons must be added");
        }

        $encoder = new JsonEncoder();

        return $encoder->encode([
            'buttons' => $this->buttons,
        ], 'json');
    }
}

<?php

namespace App\Service\Vk;

use App\Entity\AnswerVariant;
use App\Enum\CallbackMethod;
use App\Service\KeyboardMaker;
use Doctrine\Common\Collections\ArrayCollection;
use InvalidArgumentException;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

class VkKeyboardMaker extends KeyboardMaker
{
    public const TEXT_BUTTON = 'text';
    public const CALLBACK_BUTTON = 'callback';

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

    public static function newCallbackButton(string $label, string $data): array
    {
        return [
            'action' => [
                'type' => self::CALLBACK_BUTTON,
                'label' => $label,
                'payload' => '{"data":"' . $data . '"}'
            ]
        ];
    }

    public static function newReplyKeyboard(array $buttons, bool $oneTime = false): array
    {
        return [
            'one_time' => $oneTime,
            'buttons' => $buttons
        ];
    }

    public static function newInlineKeyboard(array $buttons): array
    {
        return [
            'inline' => true,
            'buttons' => $buttons
        ];
    }

    public static function newEmptyReplyKeyboard(): array
    {
        return [
            'buttons' => [],
            'one_time' => true
        ];
    }

    public static function newEmptyInlineKeyboard(): array
    {
        return [
            'inline' => true,
            'buttons' => []
        ];
    }

    // todo ниже методы. Если в KeyboardMaker, то не работают

    public static function newChooseSurveyKeyboard(int $surveysCount): mixed
    {
        $buttons = [];
        for ($i = 1; $i <= $surveysCount; $i++) {
            $buttons[] = self::newTextButton($i);
        }

        $buttons = array_merge(
            [[
                self::newTextButton('Обновить список опросов')
            ]],
            array_chunk($buttons, 5)
        );

        return self::newReplyKeyboard($buttons);
    }

    public static function newSurveyOptionsKeyboard(): mixed
    {
        return self::newReplyKeyboard([
            [self::newTextButton('Начать')],
            [self::newTextButton('Показать вопросы')],
            [self::newTextButton('Отмена')],
        ]);
    }

    public static function newAnsweringOptionsKeyboard(bool $nextQuestionAvailable = false): mixed
    {
        $buttons = [];

        if ($nextQuestionAvailable) {
            $buttons[] = [self::newTextButton('Следующий вопрос')];
        }

        $buttons[] = [self::newTextButton('Отменить заполнение анкеты')];

        return self::newReplyKeyboard($buttons);
    }

    public static function newChooseAnswerVariantsKeyboard(int $questionId, iterable $variants): mixed
    {
        if (count($variants) === 0) {
            return null;
        }
        
        $flatButtons = [];

        $i = 1;
        /** @var AnswerVariant $variant */
        foreach ($variants as $variant) {
            $flatButtons[] = self::newCallbackButton($i++, CallbackMethod::CHOOSE_ANSWER . ' ' . $questionId . ' ' . $variant->getId());
        }

        return self::newInlineKeyboard(array_chunk($flatButtons, 5));
    }

    public static function newFormCompletedOptionsKeyboard(): mixed
    {
        return self::newReplyKeyboard([
            // [self::newTextButton('Показать ответы')],
            [self::newTextButton('Сохранить анкету')],
            [self::newTextButton('Отменить участие')],
        ]);
    }
}

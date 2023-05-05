<?php

namespace App\Service\Telegram;

use App\Enum\CallbackMethod;
use App\Service\KeyboardMaker;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use TelegramBot\Api\Types\ReplyKeyboardMarkup;

class TelegramKeyboardMaker extends KeyboardMaker
{
    public function __construct() {
    }

    public static function newTextButton(string $label): array
    {
        return [
            'text' => $label
        ];
    }

    public static function newCallbackButton(string $label, string $data): array
    {
        return [
            'text' => $label,
            'callback_data' => $data
        ];
    }

    public static function newReplyKeyboard(array $buttons, bool $oneTime = false): mixed
    {
        return new ReplyKeyboardMarkup(
            keyboard: $buttons,
            oneTimeKeyboard: $oneTime
        );
    }

    public static function newInlineKeyboard(array $buttons): mixed
    {
        return new InlineKeyboardMarkup($buttons);
    }

    public static function newEmptyReplyKeyboard(): ReplyKeyboardMarkup
    {
        return new ReplyKeyboardMarkup([]);
    }

    public static function newEmptyInlineKeyboard(): InlineKeyboardMarkup
    {
        return new InlineKeyboardMarkup([]);
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

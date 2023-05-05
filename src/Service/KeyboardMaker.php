<?php

namespace App\Service;

abstract class KeyboardMaker
{
    abstract public static function newTextButton(string $label): array;

    abstract public static function newCallbackButton(string $label, string $data): array;

    abstract public static function newReplyKeyboard(array $buttons, bool $oneTime = false): mixed;

    abstract public static function newInlineKeyboard(array $buttons): mixed;

    abstract public static function newEmptyReplyKeyboard(): mixed;

    abstract public static function newEmptyInlineKeyboard(): mixed;

    abstract public static function newChooseSurveyKeyboard(int $surveysCount): mixed;
    // {
    //     $flatButtons = [];

    //     for ($i = 1; $i <= $surveysCount; $i++) {
    //         $flatButtons = self::newTextButton($i);
    //     }

    //     return self::newReplyKeyboard(array_chunk($flatButtons, 5));
    // }

    // public static function newSurveyOptionsKeyboard(): mixed
    // {
    //     return self::newReplyKeyboard([
    //         [self::newTextButton('Начать')],
    //         [self::newTextButton('Показать вопросы')],
    //         [self::newTextButton('Отмена')],
    //     ]);
    // }

    // public static function newAnsweringOptionsKeyboard(bool $nextQuestionAvailable = false): mixed
    // {
    //     $buttons = [];

    //     if ($nextQuestionAvailable) {
    //         $buttons[] = [self::newTextButton('Следующий вопрос')];
    //     }

    //     $buttons[] = [self::newTextButton('Отменить заполнение анкеты')];

    //     return self::newReplyKeyboard($buttons);
    // }


    // public static function newChooseAnswerVariantsKeyboard(int $variantsCount): mixed
    // {
    //     $flatButtons = [];

    //     for ($i = 1; $i <= $variantsCount; $i++) {
    //         $flatButtons = self::newCallbackButton($i, CallbackMethod::CHOOSE_ANSWER . ' ' . $i);
    //     }

    //     return self::newInlineKeyboard(array_chunk($flatButtons, 5));
    // }

    // public static function newFormCompletedOptionsKeyboard(): mixed
    // {
    //     return self::newReplyKeyboard([
    //         [self::newTextButton('Показать ответы')],
    //         [self::newTextButton('Сохранить анкету')],
    //         [self::newTextButton('Отменить участие')],
    //     ]);
    // }
}

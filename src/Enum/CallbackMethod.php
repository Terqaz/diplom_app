<?php

namespace App\Enum;

class CallbackMethod implements EnumerationInterface
{
    public const CHOOSE_ANSWER = 'ch_ans';

    public const TYPES = [
        self::CHOOSE_ANSWER
    ];

    public static function getTypes(): array
    {
        return self::TYPES;
    }
}

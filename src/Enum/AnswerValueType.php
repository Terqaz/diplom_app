<?php

namespace App\Enum;

class AnswerValueType implements EnumerationInterface
{
    public const STRING = 'string';
    public const INTEGER = 'integer';
    public const NUMBER = 'number';

    public const TYPES = [
        self::STRING,
        self::INTEGER,
        self::NUMBER,
    ];

    public static function getTypes(): array
    {
        return self::TYPES;
    }
}

<?php

namespace App\Enum;

class QuestionType implements EnumerationInterface
{
    public const CHOOSE_ONE = 'choose_one';
    public const CHOOSE_MANY = 'choose_many';
    public const CHOOSE_ORDERED = 'choose_ordered';
    public const CHOOSE_ALL_ORDERED = 'choose_all_ordered';

    public const TYPES = [
        self::CHOOSE_ONE,
        self::CHOOSE_MANY,
        self::CHOOSE_ORDERED,
        self::CHOOSE_ALL_ORDERED
    ];

    public static function getTypes(): array {
        return self::TYPES;
    }
}

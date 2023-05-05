<?php

namespace App\Enum;

class SocialNetworkCode implements EnumerationInterface
{
    public const TELEGRAM = 'tg';
    public const VKONTAKTE = 'vk';

    public const TYPES = [
        self::TELEGRAM,
        self::VKONTAKTE,
    ];

    public static function getTypes(): array {
        return self::TYPES;
    }
}

<?php

namespace App\Enum;

class AccessProperty
{
    public const EMAIL = 'email';
    public const PHONE = 'phone';

    public const TYPES = [
        self::EMAIL,
        self::PHONE
    ];
}

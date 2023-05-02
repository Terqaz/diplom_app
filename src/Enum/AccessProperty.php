<?php

namespace App\Enum;

class AccessProperty
{
    public const EMAIL_PROPERTY = 'email';
    public const PHONE_PROPERTY = 'phone';

    public const TYPES = [
        self::EMAIL_PROPERTY,
        self::PHONE_PROPERTY
    ];
}

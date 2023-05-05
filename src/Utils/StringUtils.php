<?php

namespace App\Utils;

class StringUtils
{
    public const EMAIL_REGEX = '/^[\dA-Za-z][.-_\dA-Za-z]+[\dA-Za-z]?@([-\dA-Za-z]+\.){1,2}[-A-Za-z]{2,7}$/';
    public const PHONE_NUMBER_REGEX = '/^((8|\+7)[\- ]?)?(\(?\d{3}\)?[\- ]?)?[\d\- ]{7,10}$/';

    private const ENCODING = 'UTF-8';

    public static function capitalize(string $s): string
    {
        return mb_convert_case($s, MB_CASE_TITLE, self::ENCODING);
    }

    public static function isEmail(string $value): bool
    {
        return preg_match(StringUtils::EMAIL_REGEX, $value);
    }

    public static function isPhone(string $value): bool
    {
        return preg_match(StringUtils::PHONE_NUMBER_REGEX, $value);
    }

    public static function extractPhoneNumber(string $value): string
    {
        return '+' + preg_replace('/[^0-9]/', '', $value);
    }
}

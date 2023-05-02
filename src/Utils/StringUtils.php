<?php

namespace App\Utils;

class StringUtils
{
    private const ENCODING = 'UTF-8';

    public static function capitalize(string $s): string
    {
        return mb_convert_case($s, MB_CASE_TITLE, self::ENCODING);
    }
}

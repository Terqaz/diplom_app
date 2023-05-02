<?php

namespace App\Enum;

class FetchWay
{
    /** Получение обновлений через метод (getUpdates) */
    public const METHOD = 0;

    /** Получение обновлений через вебхук */
    public const WEBHOOK = 1;
}

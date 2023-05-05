<?php

namespace App\Enum;

/**
 * В публичном опросе возможен R:
 * 1. Для ANONYM:
 *  - Основной информации
 *  - Заполненные анкеты (без фильтров) файлом
 * 2. Для VIEWER:
 *  - Заполненные анкеты (с фильтрами)
 *  - Статистика
 *  - Вопросы
 * 
 * В публичном боте возможен R:
 * 1. Для ANONYM:
 *  - Основной информации
 *  - Список только публичных опросов
 * 2. Для VIEWER:
 *  - Список всех опросов
 * 
 * Независимо от приватности (бота или опроса):
 *  - C - нужны права AUTHORIZED для бота и QUESTIONER для опроса
 *  - U - нужны права ADMIN для бота и QUESTIONER для опроса
 *  - D (только опрос) - нужны права QUESTIONER
 * 1. Если приватный (бот или опрос):
 *  - R - нужны права VIEWER
 * 2. Если публичный:
 *  - R - любые пользователи
 */
class UserRole implements EnumerationInterface
{
    /** Администратор (в боте) */
    public const ADMIN = 'admin';

    /** Анкетер (в боте или опросе) */
    public const QUESTIONER = 'questioner';

    /** Просматривающий результаты (в боте или опросе) */
    public const VIEWER = 'viewer';

    /** Авторизованный пользователь */
    public const AUTHORIZED = 'authorized';

    /** Аноним */
    public const ANONYM = 'anonym';

    /** Роль с более высоким приоритетом имеет больше прав */
    private const PRIORITIES = [
        self::ADMIN => 4,
        self::QUESTIONER => 3,
        self::VIEWER => 2,
        self::AUTHORIZED => 1,
        self::ANONYM => 0
    ];

    public static function getTypes(): array
    {
        return [
            self::ADMIN,
            self::QUESTIONER,
            self::VIEWER,
            self::AUTHORIZED,
            self::ANONYM
        ];
    }

    /** Проверить, что пользователь с ролью $userRole имеет права роли $desiredRole */
    public static function isGranted(string $userRole, string $desiredRole): bool
    {
        return self::PRIORITIES[$userRole] >= self::PRIORITIES[$desiredRole];
    }
}

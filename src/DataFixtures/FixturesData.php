<?php

namespace DataFixtures;

use App\Entity\Bot;
use App\Entity\Question;
use App\Entity\RespondentAnswer;
use App\Entity\Schedule;
use App\Entity\Survey;
use App\Entity\SurveyIteration;

class FixturesData
{
    public const LAST_NAMES = [
        'Макаров', 'Андреев', 'Ковалёв', 'Ильин', 'Гусев', 'Титов', 'Кузьмин', 'Кудрявцев', 'Баранов', 'Куликов',
        'Сорокин', 'Захаров', 'Борисов', 'Королёв'
    ];

    public const FIRST_NAMES = [
        'male' => [
            'Михаил', 'Никита', 'Матвей', 'Роман', 'Егор', 'Арсений', 'Иван', 'Евгений', 'Даниил', 'Тимофей',
            'Владислав', 'Игорь', 'Владимир', 'Павел', 'Руслан', 'Марк',
        ],
        'female' => [
            'Анна', 'Мария', 'Елена', 'Дарья', 'Алина', 'Ирина', 'Екатерина', 'Арина', 'Полина', 'Ольга', 'Юлия',
            'Татьяна', 'Наталья', 'Виктория', 'Елизавета', 'Ксения', 'Милана', 'Вероника', 'Алиса', 'Валерия',
            'Александра', 'Ульяна', 'Кристина', 'София', 'Марина'
        ]
    ];

    public const PATRONYMICS = [
        'male' => [
            'Михайлович', 'Матвеевич', 'Романович', 'Егоров', 'Иванович', 'Евгеньевич', 'Данилович', 'Тимофеевич',
            'Владиславович', 'Игоревич', 'Владимирович', 'Павлович', 'Русланович'
        ],
        'female' => [
            'Михайловна', 'Матвеевна', 'Романовна', 'Егоровна', 'Ивановна', 'Евгеньевна', 'Даниловна', 'Тимофеевна',
            'Владиславовна', 'Игоревна', 'Владимировна', 'Павловна', 'Руслановна'
        ]
    ];

    /** 
     * Структура: surveyNumber => data
     * 
     * @see Survey
     * @see Question
     * @see Schedule
     */
    public const SURVEYS = [
        0 => [
            'title' => 'Нужно ли вам больше лавочек?',
            'schedule' => [ // 10-го января и июня в 15:00
                'type' => Schedule::DURING_YEAR,
                'repeatValues' => "[[1, 6], 10, '15:00']",
                'isNoticeOnStart' => true
            ],
            'questions' => [
                0 => [
                    'type' => Question::CHOOSE_ONE,
                    'canGiveOwnAnswer' => true,
                    'serialNumber' => 1,
                    'title' => 'Напишите вашу фамилию',
                ],
                1 => [
                    'type' => Question::CHOOSE_ONE,
                    'canGiveOwnAnswer' => false,
                    'serialNumber' => 2,
                    'title' => 'Выберите ваш факультет',
                    'variants' => ['ФАИ', 'ИМиТ', 'МИ', 'ИСНЭП', 'ИСФ', 'ФТФ', 'ЗФ', 'ФДО', 'УК', 'НИИ']
                ],
                2 => [
                    'type' => Question::CHOOSE_ONE_RANGED,
                    'canGiveOwnAnswer' => false,
                    'serialNumber' => 3,
                    'title' => 'Введите номер курса',
                    'intervalBorders' => '1-6',
                ],
                3 => [
                    'type' => Question::CHOOSE_MANY,
                    'canGiveOwnAnswer' => false,
                    'serialNumber' => 4,
                    'title' => 'Перечислите корпуса, в которых вы чаще всего бываете',
                    'variants' => ['1', '2', '3', '4', '5', '9', 'Административный', 'Аудиторный', 'Спортивный комплекс'],
                    'maxVariants' => 3,
                ],
                4 => [
                    'type' => Question::CHOOSE_ONE,
                    'canGiveOwnAnswer' => false,
                    'serialNumber' => 5,
                    'title' => 'Как часто бывают заняты лавочки в указанных вами корпусах?',
                    'variants' => ['Никогда', 'Иногда', 'Редко', 'Часто', 'Всегда'],
                ],
                5 => [
                    'type' => Question::CHOOSE_ONE,
                    'canGiveOwnAnswer' => false,
                    'serialNumber' => 6,
                    'title' => 'Как вы считаете, нужно ли установить в данных корпусах больше лавочек?',
                    'variants' => ['Нет', 'Не знаю', 'Да'],
                ]
            ],
        ],
        1 => [
            'title' => 'Опрос о качестве образования в осеннем семестре 2022',
            'questions' => [],
            'isPrivate' => true,
            'jumpConditions' => [
                'subconditions' => []
            ]
        ],
        2 => [
            'title' => 'Кого повесить на доску почета?'
        ]
    ];

    /** 
     * Структура: surveyNumber => respondentNumber => questionNumber => data
     * Если выбран вариант, то data: ['variant' => variantNumber]
     * Если выбран варианты, то data: ['variants' => [...variantNumbers]]
     * 
     * @see Question
     * @see RespondentAnswer
     */
    public const ANSWERS = [
        0 => [
            0 => [
                0 => ['value' => 'Иванов'],
                1 => ['variant' => 0], // ФАИ
                2 => ['value' => 4],
                3 => ['variants' => [0, 1]], // 1, 2
                4 => ['variant' => 1], // Иногда
                5 => ['variant' => 2], // Да
            ],
            1 => [
                0 => ['value' => 'Романов'],
                1 => ['variant' => 0], // ФАИ
                2 => ['value' => 2],
                3 => ['variants' => [0, 1, 7]], // 1, 2, Аудиторный
                4 => ['variant' => 3], // Часто
                5 => ['variant' => 2], // Да
            ],
            2 => [
                0 => ['value' => 'Данилов'],
                1 => ['variant' => 2], // МИ
                2 => ['value' => 4],
                3 => ['variants' => [0, 5]], // 1, 9
                4 => ['variant' => 2], // Редко
                5 => ['variant' => 0], // Нет
            ],
        ],
    ];

    /** 
     * Структура: botNumber => data
     * 
     * @see Bot 
     */
    public const BOTS = [
        0 => [
            'title' => 'Опросы ЛГТУ',
            'description' => 'Официальный бот ЛГТУ',
            'surveys' => [0, 1]
        ],
        1 => [
            'title' => 'Опросы ФАИ',
            'description' => 'Здесь мы проводим очень интересные опросы',
            'surveys' => [2]
        ],
        2 => [],
        3 => [],
    ];
}

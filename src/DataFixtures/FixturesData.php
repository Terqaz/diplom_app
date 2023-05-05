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
        2 => [
            'title' => 'Опросы ВЦИОМ',
            'description' => "Всероссийский (до 1992 года - Всесоюзный) центр изучения общественного мнения (ВЦИОМ) - старейшая и наиболее известная российская компания, проводящая опросы общественного мнения.\n\nВЦИОМ был основан в декабре 1987 г. при Министерстве Труда СССР и ВЦСПС. В 1998 году Центр был перерегистрирован как государственное унитарное предприятие, в 1999 году Центру был присвоен статус научного учреждения. В 2003 г. ФГУП «ВЦИОМ» был преобразован в открытое акционерное общество со стопроцентным государственным капиталом.\n\nВ 2017 году Всероссийский центр изучения общественного мнения (ВЦИОМ) отмечает 30-летний юбилей своей деятельности.\n\nСегодня ВЦИОМ - ведущая российская исследовательская организация в области общественного мнения.",
            'surveys' => [3]
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
            'description' => 'Данный опрос предназначен для определения нехватки лавочек в университетских корпусах по мнению студентов',
            'isEnabled' => true,
            'schedule' => [ // 10-го января и июня в 15:00
                'type' => Schedule::DURING_YEAR,
                'repeatValues' => "[[1, 6], 10, '15:00']",
                'isNoticeOnStart' => true
            ],
            'elements' => [
                0 => [
                    'type' => Question::CHOOSE_ONE,
                    'ownAnswersCount' => 1,
                    'serialNumber' => 1,
                    'title' => 'Напишите вашу фамилию',
                ],
                1 => [
                    'type' => Question::CHOOSE_ONE,
                    'serialNumber' => 2,
                    'title' => 'Выберите ваш факультет',
                    'variants' => ['ФАИ', 'ИМиТ', 'МИ', 'ИСНЭП', 'ИСФ', 'ФТФ', 'ЗФ', 'ФДО', 'УК', 'НИИ']
                ],
                2 => [
                    'type' => Question::CHOOSE_ONE_RANGED,
                    'serialNumber' => 3,
                    'title' => 'Введите номер курса',
                    'intervalBorders' => '1-6',
                ],
                3 => [
                    'type' => Question::CHOOSE_MANY,
                    'serialNumber' => 4,
                    'title' => 'Перечислите корпуса, в которых вы чаще всего бываете',
                    'variants' => ['1', '2', '3', '4', '5', '9', 'Административный', 'Аудиторный', 'Спортивный комплекс'],
                    'maxVariants' => 3,
                ],
                4 => [
                    'type' => Question::CHOOSE_ONE,
                    'serialNumber' => 5,
                    'title' => 'Как часто бывают заняты лавочки в указанных вами корпусах?',
                    'variants' => ['Никогда', 'Иногда', 'Редко', 'Часто', 'Всегда'],
                ],
                5 => [
                    'type' => Question::CHOOSE_ONE,
                    'serialNumber' => 6,
                    'title' => 'Как вы считаете, нужно ли установить в данных корпусах больше лавочек?',
                    'variants' => ['Нет', 'Не знаю', 'Да'],
                ]
            ],
        ],
        1 => [
            'title' => 'Опрос о качестве образования в осеннем семестре 2022',
            'elements' => [],
            'isPrivate' => true
        ],
        2 => [
            'title' => 'Кого повесить на доску почета?'
        ],
        3 => [
            'title' => 'Анкета социология здоровья',
            'description' => 'Данный опрос предназначен оценки медицины в России',
            'elements' => [
                0 => [
                    'serialNumber' => 1,
                    'type' => Question::CHOOSE_MANY,
                    'title' => 'Вы обращались или не обращались в течение последнего года в государственные, муниципальные, частные медицинские учреждения для получения медицинской помощи?',
                    'isRequired' => false,
                    'maxVariants' => 3,
                    'variants' => [
                        'Да, обращался в государственные, муниципальные',
                        'Да, обращался в частные',
                        'Да, обращался в ведомственные',
                        'Нет, не обращался',
                    ],
                ],
                // Пропускаем второй вопрос, если ответили не первый вариант из прошлого вопроса
                1 => [
                    'isJump' => true,
                    'serialNumber' => 2,
                    'toQuestion' => 3, // ключ из массива elements
                    'subconditions' => [
                        0 => [
                            'isEqual' => false,
                            'answerVariant' => [0, 0] // ключ из массива elements и индекс варианта ответа на этот вопрос из variants
                        ]
                    ]
                ],
                2 => [
                    'serialNumber' => 3,
                    'type' => Question::CHOOSE_ONE,
                    'title' => 'Скажите, пожалуйста, Вы в целом остались довольны или недовольны оказанной Вам медицинской помощью в государственных/ муниципальных медицинских учреждениях?',
                    'isRequired' => false,
                    'maxVariants' => 1,
                    'variants' => [
                        'Полностью доволен',
                        'По большей части доволен',
                        'Отчасти доволен, отчасти – нет',
                        'По большей части не доволен',
                        'Совершенно не доволен',
                    ],
                ],
                3 => [
                    'serialNumber' => 4,
                    'type' => Question::CHOOSE_ONE,
                    'title' => 'Российское здравоохранение сейчас…',
                    'isRequired' => false,
                    'maxVariants' => 1,
                    'variants' => [
                        'Не развивается/деградирует',
                        'Догоняет по всем направлениям мировую медицину',
                        'В каких-то областях обогнала мировую медицину',
                    ],
                ],
                4 => [
                    'serialNumber' => 5,
                    'type' => Question::CHOOSE_MANY,
                    'title' => 'В каких трёх из этих направлений Вы бы хотели увидеть наибольший прогресс в ближайшие пять лет?',
                    'isRequired' => false,
                    'ownAnswersCount' => 1,
                    'maxVariants' => 3,
                    'variants' => [
                        'Телемедицина',
                        'Трансплантология (пересадка органов и тканей)',
                        'Генные разработки',
                        'Репродуктивные технологии, ЭКО и пр.',
                        'Разработки высокотехнологичного медоборудования',
                        'Вирусология',
                        'Поиск лекарства от рака',
                        'Косметология, пластическая хирургия',
                        'Борьба со старением',
                    ],
                ]
            ],
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
}

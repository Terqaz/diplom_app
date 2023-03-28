<?php

namespace App\DataFixtures;

use App\Entity\Bot;
use App\Entity\Respondent;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Exception;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $userPasswordHasher;
    private ObjectManager $manager;

    /**
     * @param UserPasswordHasherInterface $userPasswordHasher
     */
    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->userPasswordHasher = $userPasswordHasher;
    }

    /**
     * @throws Exception
     */
    public function load(ObjectManager $manager): void
    {
        $this->manager = $manager;

        $users = [];
        for ($i = 1; $i <= 30; $i++) {
            $user = $this->createUser($i);
            if ($i < 5) {
                $user->setRoles(['ROLE_SUPER_ADMIN']);
            }
            $users[] = $user;
            $this->manager->persist($user);
        }
        $respondents = [];
        for ($i = 1; $i <= 100; $i++) {
            $respondent = $this->createRespondent($i);
            $respondents[] = $respondent;
            $this->manager->persist($respondent);
        }
        $privateBots = [];
        foreach (self::BOTS_DATA as $botData) {
            $privateBots[] = $this->createBot($botData, true);
        }
        $publicBots = [];
        foreach (self::BOTS_DATA as $botData) {
            $privateBots[] = $this->createBot($botData, false);
        }

        $manager->flush();
    }

    private const USER_LAST_NAMES = [
        'Макаров', 'Андреев', 'Ковалёв', 'Ильин', 'Гусев', 'Титов', 'Кузьмин', 'Кудрявцев', 'Баранов', 'Куликов',
        'Сорокин', 'Захаров', 'Борисов', 'Королёв'
    ];

    private const USER_FIRST_NAMES = [
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

    private const USER_PATRONYMICS = [
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
     * @throws Exception
     */
    private function createUser(int $i): User
    {
        $user = new User();

        if (random_int(0, 1) === 1) { // male
            $user
                ->setLastName(self::randElement(self::USER_LAST_NAMES))
                ->setFirstName(self::randElement(self::USER_FIRST_NAMES['male']));
            if (random_int(1, 100) > 85) {
                $user->setPatronymic(self::randElement(self::USER_PATRONYMICS['male']));
            }
        } else { // female
            $user
                ->setLastName(self::randElement(self::USER_LAST_NAMES) . 'а')
                ->setFirstName(self::randElement(self::USER_FIRST_NAMES['female']));
            if (random_int(1, 100) > 85) {
                $user->setPatronymic(self::randElement(self::USER_PATRONYMICS['female']));
            }
        }
        if (random_int(0, 1) === 1) {
            $user->setPhone($this->createPhone());
        }
        $user->setEmail($this->createEmail('user.' . $i));
        $user->setPassword(
            $this->userPasswordHasher->hashPassword(
                $user,
                'password' . $i
            )
        );
        return $user;
    }

    /**
     * @throws Exception
     */
    private function createRespondent(int $i): Respondent
    {
        $respondent = new Respondent();

        if (random_int(0, 1) === 1) {
            $respondent->setVkontakteId(random_int(0, 2 ** 32));
        } else {
            $respondent->setTelegramId(random_int(0, 2 ** 32));
        }

        if (random_int(0, 1) === 1) {
            $respondent->setEmail($this->createEmail('respondent.' . $i));
        }
        if (random_int(0, 1) === 1) {
            $respondent->setPhone($this->createPhone());
        }

        return $respondent;
    }

    const BOTS_DATA = [
        1 => [
            'title' => 'Опросы ЛГТУ',
            'description' => 'Официальный бот ЛГТУ',
            'surveys' => [
                [
                    'title' => 'Опрос о качестве образования в осеннем семестре 2022',
                    'questions' => [

                    ],
                    'jumpConditions' => [
                        'subconditions' => [

                        ]
                    ],
                ], [
                    'title' => 'Нужно ли вам больше лавочек?',
                    'questions' => [
                        [
                            'serialNumber' => 1,
                            'title' => 'Напишите вашу фамилию',
                            'canGiveOwnAnswer' => true
                        ], [
                            'serialNumber' => 2,
                            'title' => 'Выберите ваш факультет',
                            'canGiveOwnAnswer' => false,
                            'variants' => ['ИМиТ', 'МИ', 'ИСНЭП', 'ИСФ', 'ФТФ', 'ФАИ', 'ЗФ', 'ФДО', 'УК', 'НИИ']
                        ], [
                            'serialNumber' => 3,
                            'title' => 'Введите номер курса',
                            'canGiveOwnAnswer' => false,
                            'intervalBorders' => '1-6'
                        ], [
                            'serialNumber' => 4,
                            'title' => 'Перечислите корпуса, в которых вы чаще всего бываете',
                            'canGiveOwnAnswer' => false,
                            'variants' => ['1', '2', '3', '4', '5', '9', 'Административный', 'Аудиторный', 'Спортивный комплекс'],
                            'maxVariants' => 3,
                        ], [
                            'serialNumber' => 5,
                            'title' => 'Как часто бывают заняты лавочки в указанных вами корпусах?',
                            'canGiveOwnAnswer' => false,
                            'variants' => ['Никогда', 'Иногда', 'Редко', 'Часто', 'Всегда'],
                        ], [
                            'serialNumber' => 6,
                            'title' => 'Как вы считаете, нужно ли установить в данных корпусах больше лавочек?',
                            'canGiveOwnAnswer' => false,
                            'variants' => ['Нет', 'Не знаю', 'Да'],
                        ]
                    ]
                ]
            ]
        ],
        2 => [
            'title' => 'Опросы ФАИ',
            'description' => 'Здесь мы проводим очень интересные опросы',
            'surveys' => [
                [
                    'title' => 'Кого повесить на доску почета?'
                ]
            ]
        ],
        3 => [

        ],
        4 => [

        ],
    ];

    private function createBot(array $botData, bool $isPrivate): Bot
    {
        $bot = new Bot();

        $bot

        return $bot;
    }

    /**
     * @throws Exception
     */
    public function createPhone(): string
    {
        return '+' . random_int(10 ** 10, 10 ** 13 - 1);
    }

    public function createEmail(string $prefix): string
    {
        return $prefix . '@example.com';
    }

    private static function randElement(array $a): mixed
    {
        return $a[array_rand($a)];
    }
}

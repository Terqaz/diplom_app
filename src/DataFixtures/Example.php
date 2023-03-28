<?php

namespace App\DataFixtures;

use App\Entity\Branch;
use App\Entity\BranchChannelUser;
use App\Entity\BranchMessage;
use App\Entity\Channel;
use App\Entity\ChannelUser;
use App\Entity\DialogMessage;
use App\Entity\Message;
use App\Entity\Tag;
use App\Entity\User;
use DateTime;
use Doctrine\Persistence\ObjectManager;
use Exception;
use RuntimeException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class Example
{
    private UserPasswordHasherInterface $userPasswordHasher;

    /**
     * @param UserPasswordHasherInterface $userPasswordHasher
     */
    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->userPasswordHasher = $userPasswordHasher;
    }

    private int $timestamp = 1654108262 - 10**8;

    /**
     * @throws Exception
     */
    private function getNextDate(): DateTime
    {
        $oldTimestamp = $this->timestamp;
        $this->timestamp += random_int(1, 4) * 10 ** 5;
        return (new DateTime())->setTimestamp($oldTimestamp);
    }

    /**
     * @throws Exception
     */
    public function load(ObjectManager $manager): void
    {
        $users = $this->createUsers($manager);

        // Некоторым сообщениям потом назначим пользовательские тэги
        // Это и личные и групповые сообщения в ветке
        $messages = [];

        foreach (self::DIALOG_MESSAGES as $messageData) {
            $message = $this->createMessage($messageData['text']);
            $manager->persist($message);
            $messages[] = $message;

            $dialogMessage = $this->createDialogMessage(
                $users[$messageData['sender']],
                $users[$messageData['receiver']],
                $message);
            $manager->persist($dialogMessage);
        }

        // Some spam
        for ($i = 1; $i <= 50; $i++) {
            $message = $this->createMessage( str_repeat('адоыпрриапдывылоридп', 50));
            $manager->persist($message);

            $dialogMessage = $this->createDialogMessage($users[10], $users[0], $message);
            $manager->persist($dialogMessage);
        }

        $channels = [];
        foreach (self::CHANNELS_DATA as $channelData) {
            // Создать пользователей канала. Первый будет создателем
            // Создать канал с указанием создателя
            // Добавить остальных пользователей в канал

            $channel = $this->createChannel($channelData);
            $channelUsers = $this->createChannelUsers($users, $manager);
            $channel->setCreator($channelUsers[0]);
            foreach ($channelUsers as $channelUser) {
                $channel->addChannelUser($channelUser);
            }
            $channel->setIsPrivate($channelData['isPrivate'] ?? false)
                ->setIsAnybodyAddBranches($channelData['setIsAnybodyAddBranches'] ?? false);

            $manager->persist($channel);
            $channels[] = $channel;

            // Создать ветки с указанием создателя
            // Добавить некоторых остальных пользователей в канал
            $branches = [];
            foreach ($channelData['branches'] as $branchData) {
                // Добавляем некоторых пользователей канала в ветку
                $channelRandomUsers = self::randElements($channelUsers, random_int(1, 10));

                $branchCreator = $channelRandomUsers[0];
                $branch = $this->createBranch($channel, $branchCreator, $branchData);
                $manager->persist($branch);

                foreach ($channelRandomUsers as $channelRandomUser) {
                    $branchChannelUser = new BranchChannelUser();
                    $branchChannelUser
                        ->setBranch($branch)
                        ->setChannelUser($channelRandomUser)
                        ->setLastSeenDate($this->getNextDate());
                    $manager->persist($branchChannelUser);
                    $branch->addBranchChannelUser($branchChannelUser);
                }

                // Добавляем сообщения с тэгами в ветку
                foreach ($branchData['messages'] as $messageData) {

                    $tags = [];
                    if (isset($messageData['tags'])) {
                        $tags = $this->createTags($messageData['tags']);
                        foreach ($tags as $tag) {
                            $manager->persist($tag);
                        }
                    }
                    $message = $this->createMessage($messageData['text'], $tags);
                    $manager->persist($message);
                    $messages[] = $message;

                    $branchMessage = $this->createBranchMessage(
                        $branch->getIsInformational() ? $branchCreator : self::randElement($channelRandomUsers),
                        $branch,
                        $message
                    );
                    $manager->persist($branchMessage);
                }
                $branches[] = $branch;

                // Некоторым сообщениям, веткам и каналам назначим пользовательские тэги
                $this->addUserTagsTo($messages, $channelRandomUsers, $manager);
                $this->addUserTagsTo($branches, $channelRandomUsers, $manager);
                $this->addUserTagsTo($channels, $channelRandomUsers, $manager);
            }
        }
        $manager->flush();
    }

    /**
     * @param ObjectManager $manager
     * @return array
     * @throws Exception
     */
    public function createUsers(ObjectManager $manager): array
    {
        $users = [];
        for ($i = 1; $i <= 20; $i++) {
            $user = new User();
            if (random_int(0, 1) === 1) { // male
                $user
                    ->setLastName(self::randElement(self::USER_LAST_NAMES))
                    ->setFirstName(self::randElement(self::USER_FIRST_NAMES['male']));
                if (random_int(1, 100) > 85) {
                    $user->setMiddleName(self::randElement(self::USER_MIDDLE_NAMES['male']));
                }
            } else { // female
                $user
                    ->setLastName(self::randElement(self::USER_LAST_NAMES) . 'а')
                    ->setFirstName(self::randElement(self::USER_FIRST_NAMES['female']));
                if (random_int(1, 100) > 85) {
                    $user->setMiddleName(self::randElement(self::USER_MIDDLE_NAMES['female']));
                }
            }
            $user
                ->setPhone('+7' . random_int(10 ** 10, 10 ** 11 - 1))
                ->setEmail('user.' . $i . '@example.com')
                ->setIsOnline(false);
            if (random_int(0, 1) === 1) {
                $user->setBirthDate(DateTime::createFromFormat('d m Y',
                    random_int(1, 28) . ' ' . random_int(1, 12) . ' ' . random_int(1950, 2012)));
            }
            if (random_int(1, 100) > 60) {
                $user->setRoles(['ROLE_ADMIN']);
            }
            $user->setPassword(
                $this->userPasswordHasher->hashPassword(
                    $user,
                    'password' . $i
                )
            );
            $users[] = $user;
            $manager->persist($user);
        }
        return $users;
    }

    /**
     * @param array $users
     * @param ObjectManager $manager
     * @return array
     * @throws Exception
     */
    public function createChannelUsers(array $users, ObjectManager $manager): array
    {
        $channelUsers = [];
        foreach (self::randElements($users, 10) as $user) {
            $channelUser = new ChannelUser();
            $channelUser
                ->setUserData($user)

                ->setEntryDate($this->getNextDate());
            $manager->persist($channelUser);
            $channelUsers[] = $channelUser;
        }
        return $channelUsers;
    }

    /**
     * @param array $channelData
     * @return Channel
     */
    public function createChannel(array $channelData): Channel
    {
        $channel = new Channel();
        $channel
            ->setName($channelData['name']);
        if (isset($channelData['description'])) {
            $channel->setDescription($channelData['description']);
        }
        return $channel;
    }

    /**
     * @param Channel $channel
     * @param ChannelUser $creator
     * @param mixed $branchData
     * @return Branch
     * @throws Exception
     */
    public function createBranch(Channel $channel, ChannelUser $creator, mixed $branchData): Branch
    {
        $branch = new Branch();
        $branch
            ->setChannel($channel)
            ->setCreator($creator)
            ->setName($branchData['name'])
            ->setIsPrivate($branchData['isPrivate'] ?? false)
            ->setIsInformational($branchData['isInformational'] ?? false)
            ->setCreationDate($this->getNextDate());
        return $branch;
    }

    /**
     * @param array $tagNames
     * @return array
     */
    public function createTags(array $tagNames): array
    {
        $tags = [];
        foreach ($tagNames as $tagName) {
            $tag = new Tag();
            $tag->setName($tagName);
            $tags[] = $tag;
        }
        return $tags;
    }

    /**
     * @param string $text
     * @param array $tags
     * @return Message
     * @throws Exception
     */
    public function createMessage(string $text, array $tags = []): Message
    {
        $message = new Message();
        $message->setText($text)
            ->setCreationDate($this->getNextDate());

        foreach ($tags as $tag) {
            $message->addTag($tag);
        }

        return $message;
    }

    /**
     * @param ChannelUser $sender
     * @param Branch $branch
     * @param Message $message
     * @return BranchMessage
     */
    public function createBranchMessage(ChannelUser $sender, Branch $branch, Message $message): BranchMessage
    {
        $branchMessage = new BranchMessage();
        $branchMessage
            ->setSender($sender)
            ->setBranch($branch)
            ->setMessage($message);
        return $branchMessage;
    }

    /**
     * @param array $objects - массив из сообщений, веток или каналов
     * @param mixed $branchUsers
     * @param ObjectManager $manager
     * @throws Exception
     */
    public function addUserTagsTo(array $objects, mixed $branchUsers, ObjectManager $manager): void
    {
        foreach (self::randElements($objects, random_int(0, count($objects))) as $object) {
            $userTags = $this->createTags(self::randElements(self::USER_TAGS, random_int(1, count(self::USER_TAGS))));

            foreach ($userTags as $userTag) {
                $userTag->setUserData(self::randElement($branchUsers)->getUserData());
                $manager->persist($userTag);
                $object->addTag($userTag);
            }
        }
    }

    private static function randElement(array $a): mixed
    {
        return $a[array_rand($a)];
    }

    private static function randElements(array $a, int $count = 1): array
    {
        if ($count < 0) {
            throw new RuntimeException();
        }
        if ($count === 0) {
            return [];
        }
        if ($count === 1) {
            return [self::randElement($a)];
        }
        $elements = [];
        foreach (array_rand($a, $count) as $key) {
            $elements[] = $a[$key];
        }
        return $elements;
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

    private const USER_MIDDLE_NAMES = [
        'male' => [
            'Михайлович', 'Матвеевич', 'Романович', 'Егоров', 'Иванович', 'Евгеньевич', 'Данилович', 'Тимофеевич',
            'Владиславович', 'Игоревич', 'Владимирович', 'Павлович', 'Русланович'
        ],
        'female' => [
            'Михайловна', 'Матвеевна', 'Романовна', 'Егоровна', 'Ивановна', 'Евгеньевна', 'Даниловна', 'Тимофеевна',
            'Владиславовна', 'Игоревна', 'Владимировна', 'Павловна', 'Руслановна'
        ]
    ];

    private const USER_TAGS = ['Важное', 'Не забыть', 'Интересное'];

    private const DIALOG_MESSAGES = [
        // Users 0 and 1
        [ 'sender' => 0, 'receiver' => 1, 'text' => 'Привет)' ],
        [ 'sender' => 1, 'receiver' => 0, 'text' => 'Привет!' ],
        [ 'sender' => 0, 'receiver' => 1, 'text' => 'Как дела?' ],
        [ 'sender' => 1, 'receiver' => 0, 'text' => 'Все в порядке' ],
        [ 'sender' => 1, 'receiver' => 0, 'text' => 'Кстати, ты не забыл позвонить брату?' ],
        [ 'sender' => 0, 'receiver' => 1, 'text' => 'Не забыл' ],

        // Users 0 and 2
        [ 'sender' => 0, 'receiver' => 2, 'text' => 'Привет!' ],
        [ 'sender' => 0, 'receiver' => 2, 'text' => 'Почему молчишь' ],
        [ 'sender' => 0, 'receiver' => 2, 'text' => 'Ответь' ],
        [ 'sender' => 0, 'receiver' => 2, 'text' => 'Ответь' ],
        [ 'sender' => 0, 'receiver' => 2, 'text' => 'Ответь' ],
        [ 'sender' => 0, 'receiver' => 2, 'text' => 'Ответь' ],

        // Users 1 and 2
        [ 'sender' => 1, 'receiver' => 2, 'text' => 'Не отвечай ему' ],
        [ 'sender' => 2, 'receiver' => 1, 'text' => 'Почему?' ],
        [ 'sender' => 1, 'receiver' => 2, 'text' => 'Не важно, просто не отвечай)' ],

        [ 'sender' => 3, 'receiver' => 0, 'text' => 'Привет, я твой подписчик, поставь лайк на аву, пожалуйста)' ],
        [ 'sender' => 4, 'receiver' => 0, 'text' => 'Привет, когда будет новое видео?' ],
        [ 'sender' => 5, 'receiver' => 0, 'text' => 'где видео?' ],
        [ 'sender' => 6, 'receiver' => 0, 'text' => 'какую игру дальше будешь записывать?' ],
        [ 'sender' => 7, 'receiver' => 0, 'text' => 'Привет, как дела? У тебя хорошо получается снимать видео. Поможешь мне начать тоже снимать?' ],
        [ 'sender' => 8, 'receiver' => 0, 'text' => 'Почему не выпускаешь новые видео?' ],
        [ 'sender' => 9, 'receiver' => 0, 'text' => 'У тебя все в порядке? Если нужна помощь, скинь номер телефона, переведу денег' ],

        // User 10 - spammer
    ];

    private const CHANNELS_DATA = [
        [
            'name' => 'Канал группы ПИ-19',
            'description' => 'Только в образовательных целях!)',
            'isPrivate' => true,
            'isAnybodyAddBranches' => true,
            'branches' => [
                [
                    'isInformational' => 0,
                    'name' => 'Мемы',
                    'messages' => [ // Сообщения
                        ['text' => 'Веселый анекдот'],
                        ['text' => 'Занятная шутка'],
                    ],
                ], [
                    'isInformational' => 0,
                    'name' => 'Флудилка',
                    'messages' => [
                        ['text' => 'Сегодня хороший день'],
                        ['text' => 'Согласен'],
                        ['text' => 'А ведь правда'],
                        ['text' => ')'],
                    ],
                ], [
                    'isInformational' => 1,
                    'name' => 'Информация по предметам',
                    'messages' => [
                        ['text' => 'Не забудьте сдать лабораторную по МА', 'tags' => ['Математический анализ']],
                        ['text' => 'Завтра не будет пары по ОС, преподаватель будет на конференции', 'tags' => ['Операционные системы']],
                    ],
                ], [
                    'isInformational' => 1,
                    'name' => 'Информация по ЛГТУ',
                    'messages' => [
                        ['text' => 'Возьмите паспорт в пятницу, нужно заполнить документы'],
                    ],
                ],
            ]
        ], [
            'name' => 'ФАИ',
            'description' => 'Почему бы и да',
            'isPrivate' => true,
            'isAnybodyAddBranches' => true,
            'branches' => [
                [
                    'isInformational' => 1,
                    'name' => 'Информация 1 курс',
                    'messages' => [
                        ['text' => 'Приглашаем всех на встречу кружка НТИ завтра в 13:20 в 404 аудитории'],
                    ],
                ], [
                    'isInformational' => 1,
                    'name' => 'Информация 2 курс',
                    'messages' => [
                        ['text' => 'Не забывайте, что вы можете принять участие в олимпиадах...'],
                    ],
                ],
            ]
        ], [
            'name' => 'Лайфхакер. Новости',
            'description' => 'Новости о кино, технологиях и скидках — обо всём, что интересно читать нам самим.',
            'branches' => [
                [
                    'isInformational' => 1,
                    'name' => 'Кино',
                    'messages' => [
                        ['text' => 'Сегодня разберем интересный факт об одном актере...', 'tags' => ['Факты', 'Об Актерах']],
                        ['text' => 'В финальных титрах фильма «Гарри Поттер и кубок огня» есть ремарка: «ни один дракон во время съёмок фильма не пострадал».', 'tags' => ['Факты']],
                        ['text' => 'Арнольд Шварценеггер, исполняя роль Терминатора, решил вовсе не моргать в фильме, так как считал, что роботы не могут этого делать.'],
                    ],
                ], [
                    'isInformational' => 1,
                    'name' => 'Технологии',
                    'messages' => [
                        ['text' => 'Топ 10 причин почему нужно брать Android', 'tags' => ['Топ', 'Android']],
                    ],
                ], [
                    'isInformational' => 0,
                    'name' => 'Обсуждение новых постов',
                    'isPrivate' => true,
                    'messages' => [
                        ['text' => 'У кого какие идеи по поводу новостей?'],
                        ['text' => 'Было бы неплохо написать про NFT', 'tags' => ['Хорошая идея']],
                        ['text' => 'Согласен'],
                    ],
                ],
            ]
        ], [
            'name' => 'ЖЮ',
            'description' => 'Самый крупный из добрых каналов в Telegram. То, что можно посмотреть перед сном.',
            'branches' => [
                [
                    'isInformational' => 1,
                    'name' => 'Кино',
                    'messages' => [
                        ['text' => 'Сегодня разберем интересный факт об одном актере...', 'tags' => ['Факты', 'Об Актерах']],
                    ],
                ],
            ]
        ], [
            'name' => 'Код дурова',
            'description' => "Новости из мира IT и Telegram.\n! Проект не ведется Павлом Дуровым",
            'branches' => []
        ], [
            'name' => 'Alpha Centauri',
            'description' => "http://youtube.com/threedaysfaq — канал на YouTube\nhttps://fb.me/thealphacentauri.net — Facebook\nhttps://twitter.com/theACentauri\nРеклама:\nhttp://bit.ly/Tele_ads",
            'branches' => []
        ], [
            'name' => 'Библиотека программиста',
            'branches' => []
        ], [
            'name' => 'ЛипецкМедиа',
            'description' => 'Моментальные новости, хорошая аналитика и много здравого смысла.',
            'branches' => []
        ], [
            'name' => 'Веб технологии ПИ-19',
            'isPrivate' => true,
            'isAnybodyAddBranches' => true,
            'branches' => []
        ],
    ];

    /**
     * @param User $sender
     * @param User $receiver
     * @param Message $message
     * @return DialogMessage
     */
    public function createDialogMessage(User $sender, User $receiver, Message $message): DialogMessage
    {
        $dialogMessage = new DialogMessage();
        $dialogMessage
            ->setSender($sender)
            ->setReceiver($receiver)
            ->setMessage($message);
        return $dialogMessage;
    }
}

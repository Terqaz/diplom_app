<?php

namespace App\DataFixtures;

use App\Entity\AnswerVariant;
use App\Entity\Bot;
use App\Entity\BotUser;
use App\Entity\Question;
use App\Entity\Respondent;
use App\Entity\RespondentAnswer;
use App\Entity\RespondentForm;
use App\Entity\Survey;
use App\Entity\SurveyUser;
use App\Entity\User;
use DataFixtures\FixturesData as Data;
use DateInterval;
use DateTime;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\Collection;
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

        /** @var Respondent[] */
        $respondents = [];
        for ($i = 1; $i <= 100; $i++) {
            $respondent = $this->createRespondent($i);
            $respondents[] = $respondent;
            $this->manager->persist($respondent);
        }

        // Создаем ботов

        $privateBots = [];

        foreach (Data::BOTS as $botData) {
            $privateBots[] = $this->createBot($botData, true, botUsersData: [
                ['user' => $users[0], 'role' => BotUser::ADMIN],
                ['user' => $users[1], 'role' => BotUser::QUESTIONER],
                ['user' => $users[2], 'role' => BotUser::VIEWER],
                ['user' => $users[3], 'role' => BotUser::VIEWER],
            ]);
        }

        $publicBots = [];

        foreach (Data::BOTS as $botData) {
            $publicBots[] = $this->createBot($botData, false, botUsersData: [
                ['user' => $users[0], 'role' => BotUser::ADMIN],
                ['user' => $users[1], 'role' => BotUser::QUESTIONER],
            ]);
        }

        // ADMIN > QUESTIONER > VIEWER > AUTHORIZED > ANONYM

        // Независимо от приватности (бота или опроса):
        //   - CU - нужны права ADMIN для бота и хотя бы QUESTIONER для опроса
        //   - D (только опрос) - нужны права ADMIN

        // 1. Если приватный (бот или опрос):
        //   - R - нужны права VIEWER
        // 2. Если публичный:
        //   - R - любые пользователи
        
        // В публичном боте возможен R:
        // 1. Для ANONYM:
        //   - Основной информации
        //   - Список только публичных опросов
        // 2. Для VIEWER:
        //   - Список всех опросов

        // В публичном опросе возможен R:
        // 1. Для ANONYM:
        //   - Основной информации
        //   - Заполненные анкеты (без фильтров) файлом
        // 2. Для VIEWER:
        //   - Заполненные анкеты (с фильтрами)
        //   - Статистика
        //   - Вопросы

        // Создаем опросы

        $surveyUsersData = [
            ['user' => $users[0], 'role' => SurveyUser::QUESTIONER],
            ['user' => $users[1], 'role' => SurveyUser::QUESTIONER],
            ['user' => $users[2], 'role' => SurveyUser::VIEWER],
            ['user' => $users[3], 'role' => SurveyUser::VIEWER],
        ];

        /** isBotPrivate => isSurveyPrivate => Surveys[] */
        $surveys = [
            true => [
                true => $this->createSurveysInBots($privateBots, $surveyUsersData, true),
                false => $this->createSurveysInBots($privateBots, $surveyUsersData, false)
            ],
            false => [
                true => $this->createSurveysInBots($publicBots, $surveyUsersData, true),
                false => $this->createSurveysInBots($publicBots, $surveyUsersData, false)
            ],
        ];

        // Добавляем ответы на опросы

        $this->addAnswersToSurveys($surveys[true][true], array_slice($respondents, 0, 3));
        $this->addAnswersToSurveys($surveys[true][false], array_slice($respondents, 0, 3));
        $this->addAnswersToSurveys($surveys[false][true], array_slice($respondents, 0, 3));
        $this->addAnswersToSurveys($surveys[false][false], array_slice($respondents, 0, 3));

        // Сохраняем данные

        $bots = array_merge($privateBots, $publicBots);
        foreach ($bots as $bot) {
            $manager->persist($bot);
        }

        $manager->flush();
    }

    /**
     * @return Survey[]
     */
    public function createSurveysInBots(array $bots, array $surveyUsersData, bool $surveysIsPrivate): array
    {
        $surveys = [];

        foreach (Data::BOTS as $i => $botData) {
            foreach ($botData['surveys'] as $surveyId) {
                $survey = $this->createSurvey(
                    Data::SURVEYS[$surveyId],
                    $surveyUsersData,
                    $surveysIsPrivate
                );

                $bots[$i]->addSurvey($survey);
                $surveys[] = $survey;
            }
        }

        return $surveys;
    }

    private function createUser(int $i): User
    {
        $user = new User();

        if (random_int(0, 1) === 1) { // male
            $user
                ->setLastName(self::randElement(Data::LAST_NAMES))
                ->setFirstName(self::randElement(Data::FIRST_NAMES['male']));
            if (random_int(1, 100) > 85) {
                $user->setPatronymic(self::randElement(Data::PATRONYMICS['male']));
            }
        } else { // female
            $user
                ->setLastName(self::randElement(Data::LAST_NAMES) . 'а')
                ->setFirstName(self::randElement(Data::FIRST_NAMES['female']));
            if (random_int(1, 100) > 85) {
                $user->setPatronymic(self::randElement(Data::PATRONYMICS['female']));
            }
        }

        if (random_int(0, 1) === 1) {
            $user->setPhone(self::createPhone());
        }

        $user->setEmail(self::createEmail('user.' . $i));
        $user->setPassword(
            $this->userPasswordHasher->hashPassword(
                $user,
                'password'
            )
        );
        return $user;
    }

    private function createRespondent(int $i): Respondent
    {
        $respondent = new Respondent();

        if (random_int(0, 1) === 1) {
            $respondent->setVkontakteId(random_int(0, 2 ** 32));
        } else {
            $respondent->setTelegramId(random_int(0, 2 ** 32));
        }

        if (random_int(0, 1) === 1) {
            $respondent->setEmail(self::createEmail('respondent.' . $i));
        }
        if (random_int(0, 1) === 1) {
            $respondent->setPhone(self::createPhone());
        }

        return $respondent;
    }

    private function createBot(array $botData, bool $isPrivate, array $botUsersData): Bot
    {
        $bot = (new Bot())
            ->setTitle($botData['title'])
            ->setDescription($botData['description'] ?? null)
            ->setIsPrivate($isPrivate);

        foreach ($botUsersData as $botUserData) {
            $bot->addBotUser(
                (new BotUser())
                    ->setUserData($botUserData['user'])
                    ->setRole($botUserData['role'])
            );
        }

        return $bot;
    }

    private function createSurvey(array $surveyData, array $surveyUsersData, bool $isPrivate): Survey
    {
        $survey = (new Survey())
            ->setTitle($surveyData['title'])
            ->setDescription($surveyData['description'] ?? null)
            ->setIsPrivate($isPrivate);

        foreach ($surveyData['questions'] as $questionData) {
            $survey->addQuestion($this->createQuestion($questionData));
        }

        foreach ($surveyUsersData as $surveyUserData) {
            $survey->addSurveyUser(
                (new SurveyUser())
                    ->setUserData($surveyUserData['user'])
                    ->setRole($surveyUserData['role'])
            );
        }

        return $survey;
    }

    private function createQuestion(array $questionData): Question
    {
        $question = (new Question())
            ->setType($questionData['type'])
            ->setOwnAnswersCount($questionData['ownAnswersCount'])
            ->setSerialNumber($questionData['serialNumber'])
            ->setTitle($questionData['title']);

        foreach ($questionData['variants'] as $i => $variantName) {
            $question->addVariant(
                (new AnswerVariant())
                    ->setSerialNumber($i) // TODO null?
                    ->setValue($variantName)
            );
        }

        return $question;
    }

    /**
     * @param Survey[] $surveys
     * @param Respondent[] $respondents
     * @return void
     */
    private function addAnswersToSurveys(array $surveys, array $respondents): void
    {
        $date = (new DateTimeImmutable())
            ->sub(new DateInterval('P6M'));

        foreach ($surveys as $si => $survey) {
            foreach ($respondents as $ri => $respondent) {
                $form = (new RespondentForm())
                    ->setSentDate($date);

                $respondent->addRespondentForm($form);
                $survey->addRespondentForm($form);

                $this->addAnswersToQuestions(
                    $respondent,
                    $survey->getQuestions(),
                    Data::ANSWERS[$si][$ri]
                );

                $date->add(new DateInterval('P1D'));
            }
        }
    }

    /**
     * @param Respondent $respondent
     * @param Collection<Question> $questions
     * @param array $answersData
     * @return void
     */
    private function addAnswersToQuestions(Respondent $respondent, Collection $questions, array $answersData): void
    {
        foreach ($questions as $qi => $question) {
            $answerData = $answersData[$qi];

            if (isset($answerData['value'])) {
                $answer = $this->createRespondentAnswer(
                    value: $answerData['value']
                );

                $question->addRespondentAnswer($answer);
                $respondent->addRespondentAnswer($answer);
            } else if (isset($answerData['variant'])) {
                $answer = $this->createRespondentAnswer(
                    variant: $question->getVariants()[$answerData['variant']]
                );

                $question->addRespondentAnswer($answer);
                $respondent->addRespondentAnswer($answer);
            } else if (isset($answerData['variants'])) {
                $serialNumber = RespondentAnswer::FIRST_SERIAL_NUMBER;

                foreach ($answerData['variants'] as $vi) {
                    $answer = $this->createRespondentAnswer(
                        variant: $question->getVariants()[$vi],
                        serialNumber: $serialNumber
                    );
                    ++$serialNumber;

                    $question->addRespondentAnswer($answer);
                    $respondent->addRespondentAnswer($answer);
                }
            }
        }
    }

    /**
     * Либо value, либо variant
     */
    private function createRespondentAnswer(?string $value = null, ?AnswerVariant $variant = null, ?int $serialNumber = null): RespondentAnswer
    {
        $answer = new RespondentAnswer();

        if (null !== $value) {
            $answer->setValue($value);
        } else if (null !== $variant) {
            $answer
                ->setAnswerVariant($variant)
                ->setSerialNumber($serialNumber);
        }

        return $answer;
    }

    private static function createPhone(): string
    {
        return '+' . random_int(10 ** 10, 10 ** 13 - 1);
    }

    private static function createEmail(string $prefix): string
    {
        return $prefix . '@example.com';
    }

    private static function randElement(array $a): mixed
    {
        return $a[array_rand($a)];
    }
}

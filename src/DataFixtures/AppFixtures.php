<?php

namespace App\DataFixtures;

use App\Entity\AnswerVariant;
use App\Entity\Bot;
use App\Entity\BotUser;
use App\Entity\JumpCondition;
use App\Entity\Question;
use App\Entity\Respondent;
use App\Entity\RespondentAnswer;
use App\Entity\RespondentForm;
use App\Entity\Schedule;
use App\Entity\Subcondition;
use App\Entity\Survey;
use App\Entity\SurveyUser;
use App\Entity\User;
use App\Enum\UserRole;
use App\DataFixtures\FixturesData as Data;
use App\Enum\AnswerValueType;
use DateInterval;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\Collection;
use Doctrine\Persistence\ObjectManager;
use Exception;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Serializer\Encoder\EncoderInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $userPasswordHasher;
    private EncoderInterface $encoder;

    /**
     * @param UserPasswordHasherInterface $userPasswordHasher
     * @param EncoderInterface $encoder
     */
    public function __construct(UserPasswordHasherInterface $userPasswordHasher, EncoderInterface $encoder)
    {
        $this->userPasswordHasher = $userPasswordHasher;
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager): void
    {
        $users = [];
        for ($i = 1; $i <= 30; $i++) {
            $user = $this->createUser($i);
            if ($i < 5) {
                $user->setRoles(['ROLE_SUPER_ADMIN']);
            }
            $users[] = $user;
            $manager->persist($user);
        }

        /** @var Respondent[] */
        $respondents = [];
        for ($i = 1; $i <= 100; $i++) {
            $respondent = $this->createRespondent($i);
            $respondents[] = $respondent;
            $manager->persist($respondent);
        }

        // Создаем ботов

        $botUsersData = [
            ['user' => $users[0], 'role' => UserRole::ADMIN],
            ['user' => $users[1], 'role' => UserRole::QUESTIONER],
            ['user' => $users[2], 'role' => UserRole::VIEWER],
            ['user' => $users[3], 'role' => UserRole::VIEWER],
        ];

        $bots = [];

        foreach (Data::BOTS as $botData) {
            $bots[] = $this->createBot($botData, $botUsersData);
        }

        // Создаем опросы

        $surveyUsersData = [
            ['user' => $users[0], 'role' => UserRole::QUESTIONER],
            ['user' => $users[1], 'role' => UserRole::QUESTIONER],
            ['user' => $users[2], 'role' => UserRole::VIEWER],
            ['user' => $users[3], 'role' => UserRole::VIEWER],
        ];

        /** isBotPrivate => isSurveyPrivate => Surveys[] */
        $surveys = $this->createSurveysInBots($bots, $surveyUsersData, false);

        $this->addAnswersToSurveys($surveys, array_slice($respondents, 0, 3));

        // Сохраняем данные

        foreach ($bots as $bot) {
            $manager->persist($bot);
        }

        $manager->flush();
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
            $respondent->setVkontakteId(random_int(0, 2 ** 31));
        } else {
            $respondent->setTelegramId(random_int(0, 2 ** 31));
        }

        if (random_int(0, 1) === 1) {
            $respondent->setEmail(self::createEmail('respondent.' . $i));
        }
        if (random_int(0, 1) === 1) {
            $respondent->setPhone(self::createPhone());
        }

        return $respondent;
    }

    private function createBot(array $botData, array $botUsersData): Bot
    {
        $bot = (new Bot())
            ->setTitle($botData['title'])
            ->setDescription($botData['description'] ?? null)
            ->setIsPrivate($botData['isPrivate'] ?? false);

        foreach ($botUsersData as $botUserData) {
            $bot->addUser(
                (new BotUser())
                    ->setUserData($botUserData['user'])
                    ->setRole($botUserData['role'])
            );
        }

        return $bot;
    }


    /**
     * @return Survey[]
     */
    public function createSurveysInBots(array $bots, array $surveyUsersData): array
    {
        $surveys = [];

        foreach (Data::BOTS as $i => $botData) {
            foreach ($botData['surveys'] as $surveyId) {
                $survey = $this->createSurvey(
                    Data::SURVEYS[$surveyId],
                    $surveyUsersData
                );

                $bots[$i]->addSurvey($survey);
                $surveys[] = $survey;
            }
        }

        return $surveys;
    }

    private function createSurvey(array $surveyData, array $surveyUsersData): Survey
    {
        $survey = (new Survey())
            ->setTitle($surveyData['title'])
            ->setDescription($surveyData['description'] ?? null)
            ->setIsPrivate($surveyData['isPrivate'] ?? false);

        $elementsData = $surveyData['elements'] ?? [];
        $elements = [];

        // Сначала создаем вопросы пропуская условия перехода
        foreach ($elementsData as $elementData) {
            $isJump = $elementData['isJump'] ?? false;
            if ($isJump) {
                $elements[] = $elementData;
                continue;
            }

            $question = $this->createQuestion($elementData);
            $survey->addQuestion($question);

            $elements[] = $question;
        }

        // Теперь создаем условия перехода, ссылаясь на вопросы
        foreach ($elements as $element) {
            if ($element instanceof Question) {
                continue;
            }

            $jumpCondition = $this->createJumpCondition($element, $elements);
            $survey->addJumpCondition($jumpCondition);
        }

        foreach ($surveyUsersData as $surveyUserData) {
            $survey->addUser(
                (new SurveyUser())
                    ->setUserData($surveyUserData['user'])
                    ->setRole($surveyUserData['role'])
            );
        }

        $scheduleData = $surveyData['schedule'] ?? null;
        if (null !== $scheduleData) {
            $survey->setSchedule(
                $this->createSchedule($scheduleData)
            );
        }

        return $survey;
    }

    private function createQuestion(array $questionData): Question
    {
        $question = (new Question())
            ->setSerialNumber($questionData['serialNumber'])
            ->setType($questionData['type'])
            ->setTitle($questionData['title'])
            ->setIsRequired($questionData['isRequired'] ?? false)
            ->setAnswerValueType($questionData['answerValueType'] ?? AnswerValueType::STRING)
            ->setIntervalBorders($questionData['intervalBorders'] ?? null)
            ->setMaxVariants($questionData['maxVariants'] ?? 1)
            ->setOwnAnswersCount($questionData['ownAnswersCount'] ?? 0);

        $variantsData = $questionData['variants'] ?? [];
        foreach ($variantsData as $i => $variantName) {
            $question->addVariant(
                (new AnswerVariant())
                    ->setSerialNumber($i)
                    ->setValue($variantName)
            );
        }

        return $question;
    }

    private function createJumpCondition(array $jumpConditionData, array $elements): JumpCondition
    {
        $jumpCondition = new JumpCondition();

        $jumpCondition
            ->setToQuestion($elements[$jumpConditionData['toQuestion']])
            ->setSerialNumber($jumpConditionData['serialNumber']);

        foreach ($jumpConditionData['subconditions'] as $i => $subconditionData) {
            [$questionIndex, $variantIndex] = $subconditionData['answerVariant'];

            /** @var AnswerVariant $variant */
            $variant = $elements[$questionIndex]->getVariants()->get($variantIndex);

            $jumpCondition->addSubcondition(
                (new Subcondition())
                    ->setSerialNumber($i)
                    ->setAnswerVariant($variant)
                    ->setIsEqual($subconditionData['isEqual'])
            );
        }

        return $jumpCondition;
    }

    /**
     * @param Survey[] $surveys
     * @param Respondent[] $respondents
     * @return void
     */
    private function addAnswersToSurveys(array $surveys, array $respondents): void
    {
        $date = (new DateTimeImmutable())
            ->sub(new DateInterval('P1M'));

        foreach ($surveys as $si => $survey) {
            foreach ($respondents as $ri => $respondent) {
                $answers = Data::ANSWERS[$si][$ri] ?? null;
                if (null !== $answers) {
                    $date = $this->addAnswersToQuestions(
                        $respondent,
                        $survey,
                        $answers,
                        $date
                    );
                }
            }
        }
    }

    /**
     * @param Respondent $respondent
     * @param Collection<Question> $questions
     * @param array $answersData
     * @return void
     */
    private function addAnswersToQuestions(Respondent $respondent, Survey $survey, array $answersData, $date): DateTimeInterface
    {
        $form = (new RespondentForm())
            ->setSentDate($date);

        $date = $date->add(new DateInterval('PT' . random_int(12, 72) . 'H'));

        $respondent->addRespondentForm($form);
        $survey->addRespondentForm($form);

        foreach ($survey->getQuestions() as $qi => $question) {
            $answerData = $answersData[$qi];

            if (isset($answerData['value'])) {
                $answer = $this->createRespondentAnswer(
                    value: $answerData['value']
                );
            } else if (isset($answerData['variant'])) {
                $answer = $this->createRespondentAnswer(
                    variant: $question->getVariants()[$answerData['variant']]
                );
            } else if (isset($answerData['variants'])) {
                $serialNumber = RespondentAnswer::FIRST_SERIAL_NUMBER;

                foreach ($answerData['variants'] as $vi) {
                    $answer = $this->createRespondentAnswer(
                        variant: $question->getVariants()[$vi],
                        serialNumber: $serialNumber
                    );
                    ++$serialNumber;
                }
            }
            
            $question->addRespondentAnswer($answer);
            $respondent->addRespondentAnswer($answer);
            $form->addAnswer($answer);
        }

        return $date;
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

    private function createSchedule(array $scheduleData): Schedule
    {
        $schedule = new Schedule;

        $schedule
            ->setType($scheduleData['type'])
            ->setRepeatValues($this->encoder->encode($scheduleData['repeatValues'], 'json'))
            ->setIsNoticeOnStart(true)
            ->setIsOnce($scheduleData['isOnce'] ?? true);

        return $schedule;
    }

    private static function createPhone(): string
    {
        return '+7' . random_int(10 ** 10, 10 ** 11 - 1);
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

<?php

namespace App\Command;

use App\Entity\Bot;
use App\Entity\Schedule;
use App\Entity\Survey;
use App\Entity\SurveyIteration;
use App\Repository\BotRepository;
use App\Repository\RespondentRepository;
use App\Repository\SurveyRepository;
use App\Service\BotClient;
use DateInterval;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Revolt\EventLoop;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Throwable;

#[AsCommand(
    name: 'app:survey:iterations-updating',
)]
class SurveyIterationsUpdatingCommand extends Command
{
    /**
     * @var array<int, Schedule>
     */
    private array $schedules = [];

    /** 
     * @var array<int, string>
     */
    private array $startSoonNotificationsTasks = [];

    /** 
     * @var array<int, string>
     */
    private array $updateIterationTasks = [];

    private SymfonyStyle $io;

    private SurveyRepository $surveyRepository;
    private DecoderInterface $decoder;
    private RespondentRepository $respondentRepository;
    private TranslatorInterface $translator;
    private EntityManagerInterface $em;

    public function __construct(
        SurveyRepository $surveyRepository,
        DecoderInterface $decoder,
        RespondentRepository $respondentRepository,
        TranslatorInterface $translator,
        EntityManagerInterface $em
    ) {
        $this->surveyRepository = $surveyRepository;
        $this->decoder = $decoder;
        $this->respondentRepository = $respondentRepository;
        $this->translator = $translator;
        $this->em = $em;

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $this->io = $io;

        $io->info('Start updating iterations');

        $this->updateScheduledSurveys();

        EventLoop::setErrorHandler(function (Throwable $t): void {
            $this->io->error([
                $t->getMessage(),
                $t->getTraceAsString()
            ]);
        });

        $suspension = EventLoop::getSuspension();

        EventLoop::repeat(10, fn () => $this->updateScheduledSurveys());

        $suspension->suspend();

        return Command::SUCCESS;
    }

    private function updateScheduledSurveys(): void
    {
        /** @var array<int, Survey> */
        $newScheduledSurveys = $this->surveyRepository
            ->createQueryBuilder('s', 's.id')
            ->select('s, sch')
            ->join('s.schedule', 'sch')
            ->where('s.isMultiple = FALSE AND s.isEnabled = TRUE')
            ->getQuery()->setHint(Query::HINT_REFRESH, true)
            ->getResult();

        // Отменяем неактуальные таски
        $this->startSoonNotificationsTasks = $this->updateTasks($this->startSoonNotificationsTasks, $newScheduledSurveys);
        $this->updateIterationTasks = $this->updateTasks($this->updateIterationTasks, $newScheduledSurveys);

        $this->schedules = array_map(
            fn (Survey $survey) => $survey->getSchedule(),
            $newScheduledSurveys
        );

        foreach ($newScheduledSurveys as $surveyId => $survey) {
            $this->delayUpdateIteration($survey);

            if (!empty($survey->getSchedule()->getNoticeBefore())) {
                $this->delayBeforeStartNotification($survey);
            }
        }

        $this->io->writeln([
            (new DateTime())->format(DateTime::ATOM),
            count($this->startSoonNotificationsTasks) . ' active survey start soon notifications',
            count($this->updateIterationTasks) . ' active update iterations tasks',
            '',
        ]);
    }

    /**
     * @param array<int, string> $tasks
     * @param array<int, Survey> $newScheduledSurveys
     * @return array
     */
    private function updateTasks(array $tasks, array $newScheduledSurveys): array
    {
        $newTasks = [];
        
        foreach ($tasks as $surveyId => $taskId) {
            $survey = $newScheduledSurveys[$surveyId] ?? null;

            if ($survey === null) {
                EventLoop::cancel($taskId);
                continue;
            }

            $oldSchedule = $this->schedules[$surveyId];
            $schedule = $survey->getSchedule();

            if (
                $schedule->getRepeatValues() !== $oldSchedule->getRepeatValues()
                || $schedule->isNoticeOnStart() !== $oldSchedule->isNoticeOnStart()
                || $schedule->getNoticeBefore() !== $oldSchedule->getNoticeBefore()
            ) {
                EventLoop::cancel($taskId);
                continue;
            }

            $newTasks[$surveyId] = $taskId;
        }

        return $newTasks;
    }

    private function delayUpdateIteration(Survey $survey): void
    {
        if (isset($this->updateIterationTasks[$survey->getId()])) {
            return;
        }

        $now = new DateTime();
        $schedule = $survey->getSchedule();

        $nextRepeat = $survey->getSchedule()->getNextRepeat();

        if (null !== $nextRepeat && $nextRepeat < $now && $schedule->isOnce()) {
            return;
        }

        if (null === $nextRepeat || $nextRepeat < $now) {
            $nextRepeat = $this->calcNextSurveyRepeat($survey->getSchedule());
            $schedule->setNextRepeat($nextRepeat);
            $this->em->persist($schedule);
            $this->em->flush();
        }

        $delay = self::calcDelaySeconds($nextRepeat);

        $this->updateIterationTasks[$survey->getId()] = EventLoop::delay(
            $delay,
            function () use ($survey, $nextRepeat): void {
                $iteration = (new SurveyIteration())
                    ->setStartDate($nextRepeat);

                $survey->addSurveyIteration($iteration);
                $this->em->persist($iteration);

                $this->em->flush();

                if ($survey->getSchedule()->isNoticeOnStart()) {
                    $this->sendStartNotification($survey);
                }

                unset($this->updateIterationTasks[$survey->getId()]);
            }
        );

        $this->io->writeln('Update iteration for survey ' . $survey->getId() .
            ' delayed to ' . $nextRepeat->format(DateTime::ATOM));
    }

    private function calcNextSurveyRepeat(Schedule $schedule): DateTimeImmutable
    {
        static $intervalsByScheduleType = [
            Schedule::DURING_DAY => new DateInterval('P1D'),
            Schedule::DURING_WEEK => new DateInterval('P7D'),
            Schedule::DURING_MONTH => new DateInterval('P1M'),
            Schedule::DURING_YEAR => new DateInterval('P1Y'),
        ];

        $prevRepeat = $schedule->getNextRepeat() ?? new DateTimeImmutable();

        $repeatValues = $this->decoder->decode(
            $schedule->getRepeatValues(),
            'json'
        );

        $now = new DateTimeImmutable();

        if ($schedule->getType() === Schedule::DURING_DAY) {
            $repeats = array_map(
                function (string $time) use ($now): DateTimeImmutable {
                    [$hour, $minute] = explode(':', $time);

                    return $now->setTime($hour, $minute);
                },
                $repeatValues
            );
        } else if (in_array($schedule->getType(), [Schedule::DURING_WEEK, Schedule::DURING_MONTH])) {
            [$days, $time] = $repeatValues;
            [$hour, $minute] = explode(':', $time);

            $repeats = array_map(
                function (int $day) use ($now, $hour, $minute): DateTimeImmutable {
                    return $now
                        ->setDate($now->format('Y'), $now->format('m'), $day)
                        ->setTime($hour, $minute);
                },
                $days
            );
        } else if ($schedule->getType() === Schedule::DURING_YEAR) {
            [$months, $day, $time] = $repeatValues;
            [$hour, $minute] = explode(':', $time);

            $repeats = array_map(
                function (int $month) use ($now, $day, $hour, $minute): DateTimeImmutable {
                    return $now
                        ->setDate($now->format('Y'), $month, $day)
                        ->setTime($hour, $minute);
                },
                $months
            );
        }

        sort($repeats);

        // Проверяем времена за текущий глобальный интервал (день, неделю, месяц, год)
        foreach ($repeats as $nextRepeat) {
            if ($nextRepeat > $prevRepeat) {
                return $nextRepeat;
            }
        }

        // Иначе берем первое время следующего глобального интервала времени
        return $repeats[0]
            ->add($intervalsByScheduleType[$schedule->getType()]);
    }

    private function sendStartNotification(Survey $survey): void
    {
        $bot = $survey->getBot();

        foreach ($bot->getSocialNetworkConfigs() as $config) {
            if (!$config->isEnabled()) {
                continue;
            }

            $botClient = BotClient::createByCode($config);

            $respondents = $this->respondentRepository->findByBotUsed($bot->getId(), $config->getCode());

            foreach ($respondents as $respondent) {
                $surveys = $this->surveyRepository->findAvailableSurveys(
                    $bot->getId(),
                    $respondent->getId()
                );

                $messageSurveysList = [];
                $i = 1;
                foreach ($surveys as $survey) {
                    $messageSurveysList[] = $i . '. ' . $survey->getTitle() . '.';
                }

                $message = $this->translator->trans('notification.survey.started', [
                    'title' => $survey->getTitle(),
                    'surveys' => implode("\n", $messageSurveysList)
                ]);

                // todo batch sendMessage
                $botClient->sendMessage(
                    $respondent->getSocialNetworkId($config->getCode()),
                    $message
                );
            }
        }
    }

    private function delayBeforeStartNotification(Survey $survey): void
    {
        $schedule = $survey->getSchedule();
        $delayDatetime = DateTime::createFromInterface($schedule->getNextRepeat())
            ->sub(new DateInterval('PT' . $schedule->getNoticeBefore() . 'M'));

        $delay = self::calcDelaySeconds($delayDatetime);

        if (
            isset($this->startSoonNotificationsTasks[$survey->getId()])
            || $delay < 0
        ) {
            return;
        }

        $this->startSoonNotificationsTasks[$survey->getId()] = EventLoop::delay(
            $delay,
            function () use ($survey): void {
                $bot = $survey->getBot();

                $message = $this->translator->trans('notification.survey.startSoon', [
                    'title' => $survey->getTitle(),
                    'value' => $survey->getSchedule()->getNoticeBefore()
                ]);

                foreach ($bot->getSocialNetworkConfigs() as $config) {
                    if (!$config->isEnabled()) {
                        continue;
                    }

                    var_dump($config->getId());
                    var_dump($config->getCode());

                    $botClient = BotClient::createByCode($config);

                    $respondents = $this->respondentRepository->findByBotUsed($bot->getId(), $config->getCode());

                    foreach ($respondents as $respondent) {
                        // todo batch sendMessage
                        $botClient->sendMessage(
                            $respondent->getSocialNetworkId($config->getCode()),
                            $message
                        );
                    }
                }

                unset($this->startSoonNotificationsTasks[$survey->getId()]);
            }
        );

        $this->io->writeln('Start soon notification for survey ' . $survey->getId() .
            ' delayed to ' . $delayDatetime->format(DateTime::ATOM));
    }

    private static function calcDelaySeconds(?DateTimeInterface $to): int
    {
        return $to->getTimestamp() - (new DateTime())->getTimestamp();
    }
}

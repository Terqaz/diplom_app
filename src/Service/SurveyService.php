<?php

namespace App\Service;

use App\Dto\BotUpdate;
use Psr\Cache\CacheItemPoolInterface;
use App\Service\SurveyStates as State;
use App\Dto\RespondentStateData;
use App\Entity\Bot;
use App\Entity\Respondent;
use App\Repository\BotRepository;
use App\Repository\RespondentRepository;
use App\Repository\SurveyRepository;
use App\Utils\StringUtils;
use LogicException;
use Symfony\Component\Serializer\SerializerInterface;

class SurveyService
{
    private CacheItemPoolInterface $telegramCache;
    private SerializerInterface $serializer;
    private SurveyRepository $surveyRepository;

    private Bot $bot;
    private Respondent $respondent;

    public function __construct(
        CacheItemPoolInterface $telegramCache,
        SerializerInterface $serializer,
        RespondentRepository $respondentRepository,
        BotRepository $botRepository,
        SurveyRepository $surveyRepository
    ) {
        $this->serializer = $serializer;
        $this->telegramCache = $telegramCache;
        $this->surveyRepository = $surveyRepository;
    }

    public function processUpdate(Bot $bot, Respondent $respondent, BotUpdate $botUpdate): void
    {
        $cacheKey = 'respondent_' . (string) $respondent->getId();
        $cacheItem = $this->telegramCache->getItem($cacheKey);

        if (!$cacheItem->isHit()) {
            if (null === $bot->getRespondentAccess($respondent)) {
                $this->processEnd();
                return;
            }
            
            // Создаем сессию
            $stateData = new RespondentStateData(State::getInitialState());
        } else {
            /** @var RespondentStateData $stateData */
            $stateData = $this->serializer->deserialize(
                $cacheItem->get(),
                RespondentStateData::class,
                'json'
            );
        }

        $this->bot = $bot;
        $this->respondent = $respondent;

        // Обрабатываем состояние
        $method = 'process' . StringUtils::capitalize($stateData->getState());
        $newStateData = $this->$method($stateData, $botUpdate);

        // Валидируем новое состояние
        $availableStates = State::GRAPH[$stateData->getState()];

        if (!in_array($newStateData->getState(), $availableStates)) {
            throw new LogicException(
                'State ' . $stateData->getState() . ' cannot be changed to ' . $newStateData->getState() . '. ' .
                    'Available states: ' . implode(', ', $availableStates)
            );
        }

        // Если у пользователя нет доступа к боту
        if (State::END === $newStateData->getState()) {
            $this->telegramCache->deleteItem($cacheKey);
            return;
        }

        // Сохраняем новое состояние
        $cacheItem->set($this->serializer->serialize($newStateData, 'json'));
        $this->telegramCache->save($cacheItem);
    }

    // public function processShowBot(RespondentStateData $data): RespondentStateData
    // {
    //      // TODO как обобщенно делать запросы к соц сети
    // }

    // public function processShowBotDescription(RespondentStateData $data): RespondentStateData
    // {
    //     # code...
    // }

    // public function processShowSurveys(RespondentStateData $data): RespondentStateData
    // {
    //     # code...
    // }

    // public function processSurveyAuth(RespondentStateData $data): RespondentStateData
    // {
    //     # code...
    // }

    // public function processShowSurvey(RespondentStateData $data): RespondentStateData
    // {
    //     # code...
    // }

    // public function processShowSurveyDescription(RespondentStateData $data): RespondentStateData
    // {
    //     # code...
    // }

    // public function processStartSurvey(RespondentStateData $data): RespondentStateData
    // {
    //     # code...
    // }

    // public function processShowQuestion(RespondentStateData $data): RespondentStateData
    // {
    //     # code...
    // }

    // public function processValidateAnswer(RespondentStateData $data): RespondentStateData
    // {
    //     # code...
    // }

    // public function processCancelSurvey(RespondentStateData $data): RespondentStateData
    // {
    //     # code...
    // }

    // public function processSurveyEnd(RespondentStateData $data): RespondentStateData
    // {
    //     # code...
    // }

    // public function processEditAnswer(RespondentStateData $data): RespondentStateData
    // {
    //     # code...
    // }

    // public function processChooseQuestion(RespondentStateData $data): RespondentStateData
    // {
    //     # code...
    // }

    // public function processShowAnswers(RespondentStateData $data): RespondentStateData
    // {
    //     # code...
    // }

    // public function processSendForm(RespondentStateData $data): RespondentStateData
    // {
    //     # code...
    // }

    protected function processEnd(): void
    {
        
    }
}

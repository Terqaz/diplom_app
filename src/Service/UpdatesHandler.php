<?php

namespace App\Service;

use App\Dto\BotUpdate;
use Psr\Cache\CacheItemPoolInterface;
use App\Service\BotStates as State;
use App\Dto\RespondentStateData;
use App\Entity\AnswerVariant;
use App\Entity\BotAccess;
use App\Entity\JumpCondition;
use App\Entity\Question;
use App\Entity\Respondent;
use App\Entity\RespondentAnswer;
use App\Entity\RespondentForm;
use App\Enum\AccessProperty;
use App\Enum\CallbackMethod;
use App\Enum\QuestionType;
use App\Enum\SocialNetworkCode;
use App\Repository\QuestionRepository;
use App\Repository\RespondentRepository;
use App\Repository\SurveyRepository;
use App\Service\Telegram\TelegramBotClient;
use App\Service\Vk\VkBotClient;
use App\Utils\StringUtils;
use DateTime;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;
use Symfony\Component\Serializer\SerializerInterface;

class UpdatesHandler
{
    private CacheItemPoolInterface $respondentSessionCache;
    private SerializerInterface $serializer;
    private RespondentRepository $respondentRepository;
    private SurveyRepository $surveyRepository;
    private MessageFormatter $messageFormatter;
    private QuestionRepository $questionRepository;
    private EntityManagerInterface $em;

    public function __construct(
        CacheItemPoolInterface $respondentSessionCache,
        SerializerInterface $serializer,
        RespondentRepository $respondentRepository,
        SurveyRepository $surveyRepository,
        MessageFormatter $messageFormatter,
        QuestionRepository $questionRepository,
        EntityManagerInterface $em
    ) {
        $this->respondentSessionCache = $respondentSessionCache;
        $this->serializer = $serializer;
        $this->surveyRepository = $surveyRepository;
        $this->respondentRepository = $respondentRepository;
        $this->messageFormatter = $messageFormatter;
        $this->questionRepository = $questionRepository;
        $this->em = $em;
    }

    public function processUpdate(BotClient $client, BotUpdate $update): void
    {
        $respondent = $this->respondentRepository->findOneByUpdate($update->getSocialNetworkCode(), $update->getFromId());
        $cacheItem = $this->respondentSessionCache->getItem(self::getRespondentCacheKey($update));

        $bot = $client->getConfig()->getBot();

        if (null === $respondent) {
            echo "New" . $update->getSocialNetworkCode() . " respondent\n";

            $respondent = new Respondent();

            $botAccess = new BotAccess();
            $respondent->addBotAccess($botAccess);

            if ($update->getSocialNetworkCode() === SocialNetworkCode::TELEGRAM) {
                $respondent->setTelegramId($update->getFromId());
            } else if ($update->getSocialNetworkCode() === SocialNetworkCode::VKONTAKTE) {
                $respondent->setVkontakteId($update->getFromId());
            }

            $bot->addRespondentAccess($botAccess);

            $this->em->persist($botAccess);
            $this->em->persist($respondent);
            $this->em->persist($bot);
            $this->em->flush();

            // Создаем сессию для нового респондента
            $stateData = new RespondentStateData(State::START);
        } else {
            if (!$cacheItem->isHit()) {
                // Создаем сессию для существующего респондента
                $stateData = new RespondentStateData(State::START);
            } else {
                echo $update->getSocialNetworkCode() . " respondent with active session " . $respondent->getId() . PHP_EOL;

                /** @var RespondentStateData $stateData */
                $stateData = $this->serializer->deserialize(
                    $cacheItem->get(),
                    RespondentStateData::class,
                    'json'
                );
            }
        }

        $stateData->setRespondent($respondent);

        echo $update->getSocialNetworkCode() .
            " respondent " . $update->getFromId() .
            ' previous state: ' . $stateData->getState() . PHP_EOL;

        // Обрабатываем состояние
        $method = 'state' . StringUtils::capitalize($stateData->getState());

        $oldState = $stateData->getState();
        $stateData = $this->$method($update, $client, $stateData);

        if ($stateData === null) {
            $this->respondentSessionCache->deleteItem(self::getRespondentCacheKey($update));
            return;
        }

        // Валидируем новое состояние
        $availableStates = State::GRAPH[$oldState];
        $availableStates[] = $oldState;

        if (!in_array($stateData->getState(), $availableStates)) {
            throw new LogicException(
                'State ' . $oldState . ' cannot be changed to ' . $stateData->getState() . '. ' .
                    'Available states: ' . implode(', ', $availableStates)
            );
        }

        echo $update->getSocialNetworkCode() .
            " respondent " . $update->getFromId() .
            ' new state data: ' . $this->serializer->serialize($stateData, 'json') . PHP_EOL;


        // Сохраняем новые данные состояния
        $cacheItem->set($this->serializer->serialize($stateData, 'json'));
        $this->respondentSessionCache->save($cacheItem);
    }

    private static function getRespondentCacheKey(BotUpdate $update): string
    {
        return $update->getSocialNetworkCode() . '_respondent_' . $update->getFromId();
    }

    protected function stateStart(BotUpdate $update, BotClient $client, RespondentStateData $stateData): RespondentStateData
    {
        $bot = $client->getConfig()->getBot();

        
        if (!$bot->isUsedByRespondent($stateData->getRespondent())) {
            // todo не приходит новому респонденту
            $client->sendMessage(
                $update->getFromId(),
                $bot->getTitle() .
                    (!empty($bot->getDescription()) ? "\n\n" . $bot->getDescription() : ''),
                $client->getKeyboardMaker()->newEmptyReplyKeyboard()
            );
        }

        if (!$bot->checkRespondentAccess($stateData->getRespondent())) {
            $client->sendMessage(
                $update->getFromId(),
                'Напишите свой email или номер телефона для получения доступа к боту',
                $client->getKeyboardMaker()->newEmptyReplyKeyboard()
            );

            return $stateData->setState(State::PERSONAL_DATA_ENTERING);
        }

        return $this->sendSurveys($update, $client, $stateData);
    }

    protected function statePersonalDataEntering(BotUpdate $update, BotClient $client, RespondentStateData $stateData): ?RespondentStateData
    {
        $bot = $client->getConfig()->getBot();

        if (StringUtils::isPhone($update->getMessageText())) {
            // Оставляем только цифры
            $phoneNumber = StringUtils::extractPhoneNumber($update->getMessageText());

            $botAccess = $bot->getRespondentAccessBy(AccessProperty::PHONE, $phoneNumber);
        } else if (StringUtils::isEmail($update->getMessageText())) {
            $email = $update->getMessageText();

            $botAccess = $bot->getRespondentAccessBy(AccessProperty::EMAIL, $email);
        } else {
            $client->sendMessage(
                $update->getFromId(),
                'Пожалуйста, проверьте правильность введенных данных'
            );

            return $stateData;
        }

        if ($botAccess === null) {
            $client->sendMessage(
                $update->getFromId(),
                'На текущий момент у вас нет доступа к данному боту. Попробуйте позже'
            );

            return $stateData;
        }

        $respondent = $stateData->getRespondent();
        $existRespondent = $botAccess->getRespondent();

        if ($existRespondent === null) {
            // убираем возможные доступы, он должен быть один
            foreach ($respondent->getBotAccesses() as $existBotAccess) {
                $respondent->removeBotAccess($existBotAccess);
                $this->em->remove($existBotAccess);
            }
            $this->em->flush();

            // Указываем известные для респондента данные
            if ($botAccess->getPropertyName() === AccessProperty::EMAIL) {
                $respondent->setEmail($botAccess->getPropertyValue());
            } else if ($botAccess->getPropertyName() === AccessProperty::PHONE) {
                $respondent->setPhone($botAccess->getPropertyValue());
            }

            $respondent->addBotAccess($botAccess);

            // привязываем респондента к существующим доступам опросов
            foreach ($bot->getSurveys() as $survey) {
                $surveyAccess = $survey->getRespondentAccessBy($botAccess->getPropertyName(), $botAccess->getPropertyValue());
                
                if ($surveyAccess !== null) {
                    $respondent->addSurveyAccess($surveyAccess);
                    $this->em->persist($surveyAccess);
                }
            }

            $this->em->persist($botAccess);
            $this->em->persist($respondent);
            $this->em->flush();

            return $this->sendSurveys($update, $client, $stateData);
        }

        $networkIdGetter = 'get' . StringUtils::capitalize(
            Respondent::SOCIAL_NETWORK_ID_FIELD[$update->getSocialNetworkCode()]
        );

        if ($existRespondent->$networkIdGetter() !== null) {
            $message = 'Указанные данные доступа уже заняты другим пользователем ';

            $message .= [
                SocialNetworkCode::TELEGRAM => 'Телеграм',
                SocialNetworkCode::VKONTAKTE => 'ВКонтакте',
            ][$update->getSocialNetworkCode()];

            $client->sendMessage($update->getFromId(), $message);

            return $stateData;
        }

        // existRespondent уже пользовался этим ботом, но в другой соц сети
        $this->em->remove($respondent);
        $this->em->flush();

        $networkIdSetter = 'set' . StringUtils::capitalize(
            Respondent::SOCIAL_NETWORK_ID_FIELD[$update->getSocialNetworkCode()]
        );

        $existRespondent
            ->$networkIdSetter($update->getFromId());

        $this->em->persist($existRespondent);
        $this->em->flush();

        $stateData->setRespondent($existRespondent);

        return $this->sendSurveys($update, $client, $stateData);
    }

    private function sendSurveys(BotUpdate $update, BotClient $client, RespondentStateData $stateData): RespondentStateData
    {
        $stateData->setState(State::SURVEY_CHOOSING);

        $surveys = $this->surveyRepository->findAvailableSurveys(
            $client->getConfig()->getBot()->getId(),
            $stateData->getRespondent()->getId()
        );

        $cachedSurveyIds = [];
        foreach ($surveys as $survey) {
            $cachedSurveyIds[] = $survey->getId();
        }
        $stateData->setAvailableSurveys($cachedSurveyIds);

        if (count($surveys) === 0) {
            $client->sendMessage(
                $update->getFromId(),
                'На данный момент нет доступных опросов! Вы можете запросить список актуальных опросов в любой момент',
                $client->getKeyboardMaker()->newChooseSurveyKeyboard(0)
            );

            return $stateData;
        }

        $message = ['Вы можете выбрать следующие опросы для прохождения:'];
        $i = 1;
        foreach ($surveys as $survey) {
            $message[] = $i . '. ' . $survey->getTitle() . '.';
        }

        $client->sendMessage(
            $update->getFromId(),
            implode("\n", $message),
            $client->getKeyboardMaker()->newChooseSurveyKeyboard(count($surveys))
        );

        return $stateData;
    }

    protected function stateSurveyChoosing(BotUpdate $update, BotClient $client, RespondentStateData $stateData): RespondentStateData
    {
        if ($update->getMessageText() === 'Обновить список опросов') {
            return $this->sendSurveys($update, $client, $stateData);
        } else if (preg_match('/^[0-9]{1,3}$/', $update->getMessageText())) {
            $surveyNumber = (int) $update->getMessageText();

            $surveyId = $stateData->getAvailableSurveys()[$surveyNumber - 1];

            if (null !== $surveyId) {
                $client->sendMessage(
                    $update->getFromId(),
                    'Выберите действие',
                    $client->getKeyboardMaker()->newSurveyOptionsKeyboard()
                );

                $stateData
                    ->setSurveyId($surveyId)
                    ->setAvailableSurveys([])
                    ->setState(State::SURVEY_CHOOSED);

                return $stateData;
            }
        }

        $client->sendMessage(
            $update->getFromId(),
            'Пожалуйста, выберите доступное действие',
            $client->getKeyboardMaker()->newChooseSurveyKeyboard(count($stateData->getAvailableSurveys()))
        );

        return $stateData;
    }

    protected function stateSurveyChoosed(BotUpdate $update, BotClient $client, RespondentStateData $stateData): RespondentStateData
    {
        if ($update->getMessageText() === 'Начать') {
            return $this->sendNextQuestion($update, $client, $stateData);
        }

        if ($update->getMessageText() === 'Показать вопросы') {
            $survey = $this->surveyRepository->find($stateData->getSurveyId());

            $client->sendMessage(
                $update->getFromId(),
                $this->messageFormatter->formatQuestions($survey->getQuestions()),
                $client->getKeyboardMaker()->newSurveyOptionsKeyboard()
            );

            return $stateData;
        }

        if ($update->getMessageText() === 'Отмена') {
            return $this->sendSurveys($update, $client, $stateData);
        }

        $client->sendMessage(
            $update->getFromId(),
            'Выберите действие',
            $client->getKeyboardMaker()->newSurveyOptionsKeyboard()
        );
        return $stateData;
    }

    protected function stateAnswering(BotUpdate $update, BotClient $client, RespondentStateData $stateData): RespondentStateData
    {
        if ($update->getCallbackId() !== null) {
            $stateData = $this->callbackAnswerProcessing($update, $client, $stateData);
        } else if ($update->getMessageText() !== null) {
            $stateData = $this->messageAnswerProcessing($update, $client, $stateData);
        }

        if ($stateData->getState() !== State::ANSWERING) {
            return $stateData;
        }

        $question = $this->questionRepository->find($stateData->getQuestionId());
        if (null === $question) {
            throw new LogicException('Question is null');
        }

        if ($this->isQuestionAnswered($question, $stateData)) {
            // Если больше нельзя дать никаких ответов, то переходим на следующий вопрос
            if (!$stateData->canGiveAnyAnswer($question->getMaxVariants(), $question->getOwnAnswersCount())) {
                return $this->sendNextQuestion($update, $client, $stateData);
            }

            // Иначе, если пользователь не знает, что можно перейти на след вопрос, то уведомляем 
            if (!$stateData->isNextQuestionNotified()) {
                $client->sendMessage(
                    $update->getFromId(),
                    'Вы можете перейти на следующий вопрос',
                    $client->getKeyboardMaker()->newAnsweringOptionsKeyboard(true)
                );
                $stateData->setNextQuestionNotified(true);
            }
        }

        return $stateData;
    }

    protected function callbackAnswerProcessing(BotUpdate $update, BotClient $client, RespondentStateData $stateData): RespondentStateData
    {
        $client->answerCallbackQuery($update->getCallbackId(), $update->getFromId(), $update->getPeerId());

        [$callbackMethod, $questionId, $variantId] = explode(' ', $update->getCallbackData());
        [$questionId, $variantId] = array_map('intval', [$questionId, $variantId]);

        if ($callbackMethod !== CallbackMethod::CHOOSE_ANSWER) {
            throw new LogicException('Wrong callback data');
        }

        // Ответ на предыдущий вопрос игнорим
        if ($questionId !== $stateData->getQuestionId()) {
            return $stateData;
        }

        $question = $this->questionRepository->find($stateData->getQuestionId());

        if ($stateData->canChooseAnswer($question->getMaxVariants())) {
            $stateData->addChoosedAnswer((int) $variantId);
            // todo убрать выбранный вариант из кнопок сообщения
        } else if ($stateData->canGiveOwnAnswer($question->getOwnAnswersCount())) {
            // Если можно дать свои, то выводим, иначе просто переход на след вопрос
            $client->sendMessage(
                $update->getFromId(),
                'Вы выбрали максимальное число вариантов'
            );
        }

        return $stateData;
    }

    protected function messageAnswerProcessing(BotUpdate $update, BotClient $client, RespondentStateData $stateData): ?RespondentStateData
    {
        $question = $this->questionRepository->find($stateData->getQuestionId());

        if ($update->getMessageText() === 'Следующий вопрос') {
            if (!$this->isQuestionAnswered($question, $stateData)) {
                $client->sendMessage(
                    $update->getFromId(),
                    'Пока что вы не можете перейти на следующий вопрос'
                );
                return $stateData;
            }

            return $this->sendNextQuestion($update, $client, $stateData);
        }

        if ($update->getMessageText() === 'Отменить заполнение анкеты') {
            $client->sendMessage(
                $update->getFromId(),
                'Вы отменили заполнение анкеты'
            );

            $stateData = (new RespondentStateData(State::SURVEY_CHOOSING))
                ->setRespondent($stateData->getRespondent());

            return $this->sendSurveys($update, $client, $stateData);
        }

        if ($question->getOwnAnswersCount() === 0 && empty($question->getIntervalBorders())) {
            $client->sendMessage(
                $update->getFromId(),
                'Вы не можете дать собственный ответ на этот вопрос'
            );
            return $stateData;
        }

        if ($stateData->canGiveOwnAnswer($question->getOwnAnswersCount()) || !empty($question->getIntervalBorders())) {
            $stateData->addOwnAnswer($update->getMessageText());
        } else if ($stateData->canChooseAnswer($question->getMaxVariants())) {
            // Если можно выбрать, то выводим, иначе просто переход на след вопрос
            $client->sendMessage(
                $update->getFromId(),
                'Вы дали максимально возможное число собственных ответов'
            );
        }

        return $stateData;
    }

    private function sendNextQuestion(BotUpdate $update, BotClient $client, RespondentStateData $stateData): RespondentStateData
    {
        // Выводим ответы на предыдущий вопрос в сообщении с вопросом
        if ($stateData->getQuestionId() !== null) {
            $answers = $stateData->getAnswersByQuestion()[$stateData->getQuestionId()] ?? null;

            if (null === $answers) {
                $answersMessageText = 'Вы пропустили вопрос №' . $stateData->getQuestionNumber();
            } else {
                $answersMessageText = $this->messageFormatter->formatAnswers(
                    $stateData->getQuestionNumber(),
                    self::orderAnswers($stateData->getAnswersByQuestion()[$stateData->getQuestionId()])
                );
            }

            // показываем ответ на предыдущем вопросе
            $client->editMessage(
                $update->getFromId(),
                $stateData->getQuestionMessageId(),
                $answersMessageText,
                $client->getKeyboardMaker()->newEmptyInlineKeyboard()
            );

            // удаляем подсказку
            $client->deleteMessage($update->getFromId(), $stateData->getActionHelpMessageId());
        }

        // Переходим на следующий вопрос
        $survey = $this->surveyRepository->find($stateData->getSurveyId());

        $choosedAnswers = $stateData->getChoosedAnswers();
        $nextFormElementNumber = $stateData->getFormElementNumber() ?? 0;

        /** @var ?Question $nextQuestion */
        $nextQuestion = null;

        while (true) {
            ++$nextFormElementNumber;

            $nextQuestion = $survey->getQuestionByNumber($nextFormElementNumber);
            if (null !== $nextQuestion) {
                break;
            }

            $jump = $survey->getJumpConditionByNumber($nextFormElementNumber);
            if (null !== $jump) {
                if ($jump->isJumpApplying($choosedAnswers)) {
                    $nextQuestion = $jump->getToQuestion();
                    $nextFormElementNumber = $nextQuestion->getSerialNumber();
                    break;
                }
            } else {
                break;
            }
        }

        // Если следующий вопрос не нашелся, то анкета заполнена
        if (null === $nextQuestion) {
            $client->sendMessage(
                $update->getFromId(),
                'Анкета заполнена. Выберите дальнейшее действие',
                $client->getKeyboardMaker()->newFormCompletedOptionsKeyboard()
            );

            return $stateData
                ->toNextQuestion(null)
                ->setState(State::FORM_COMPLETED);
        }

        $questionNumber = $this->surveyRepository->findQuestionNumber($survey->getId(), $nextQuestion->getSerialNumber());
        $stateData->toNextQuestion($nextQuestion->getId(), $questionNumber, $nextFormElementNumber);

        $questionMessageText = $this->messageFormatter->formatFullQuestion($nextQuestion, $questionNumber);

        $isQuestionAnswered = $this->isQuestionAnswered(
            $survey->getQuestionByNumber($nextFormElementNumber),
            $stateData
        );

        // Отправляем сообщение с inline кнопами с вариантами ответов
        $questionMessageId = $client->sendMessage(
            $update->getFromId(),
            messageText: $questionMessageText,

            keyboard: $client->getKeyboardMaker()->newChooseAnswerVariantsKeyboard(
                $nextQuestion->getId(), // Нужно знать, на какой вопрос выбираем ответ, чтобы он не попал на следующий вопрос
                $nextQuestion->getVariants()
            )
        );

        // Затем сообщение с reply кнопками для выбора действия (обязательно 2 разных сообщения)
        $actionHelpMessageId = $client->sendMessage(
            $update->getFromId(),
            'Дайте ответ на вопрос или выберите дальнейшее действие',
            $client->getKeyboardMaker()->newAnsweringOptionsKeyboard($isQuestionAnswered)
        );

        return $stateData
            ->setQuestionMessageId($questionMessageId)
            ->setActionHelpMessageId($actionHelpMessageId)
            // ->setQuestionMessageText($questionMessageText)
            ->setNextQuestionNotified($isQuestionAnswered) // если необходимый ответ дан, то не нужно уведомлять
            ->setState(State::ANSWERING);
    }

    /**
     * Дан ли требуемый минимальный ответ на вопрос
     */
    public function isQuestionAnswered(Question $question, RespondentStateData $stateData): bool
    {
        if (!$question->isRequired()) {
            return true;
        }

        if (!isset($stateData->getAnswersByQuestion()[$question->getId()])) {
            return false;
        }

        $answers = $stateData->getAnswersByQuestion()[$question->getId()];

        return $question->getType() === QuestionType::CHOOSE_ONE
            && (count($answers['choosed']) === 1 || count($answers['own']) === 1)

            || in_array($question->getType(), [QuestionType::CHOOSE_MANY, QuestionType::CHOOSE_ORDERED], true)
            && (count($answers['choosed']) >= 1 || count($answers['own']) >= 1)

            || $question->getType() === QuestionType::CHOOSE_ALL_ORDERED
            && (count($answers['choosed']) === $question->getVariants()->count());
    }

    protected function stateFormCompleted(BotUpdate $update, BotClient $client, RespondentStateData $stateData): ?RespondentStateData
    {
        if ($update->getMessageText() === 'Сохранить анкету') {
            $survey = $this->surveyRepository->find($stateData->getSurveyId());
            $respondent = $stateData->getRespondent();

            $form = (new RespondentForm())
                ->setSentDate(new DateTime());
            $survey->addRespondentForm($form);
            $respondent->addRespondentForm($form);

            $this->em->persist($form);

            foreach ($stateData->getAnswersByQuestion() as $questionId => $answers) {
                $question = $this->questionRepository->find($questionId);

                $orderedAnswers = self::orderAnswers($answers);

                foreach ($orderedAnswers as $i => [$isChoosed, $answer]) {
                    $respondentAnswer = (new RespondentAnswer())
                        ->setSerialNumber($i);
                    $respondent->addRespondentAnswer($respondentAnswer);
                    $question->addRespondentAnswer($respondentAnswer);
                    $form->addAnswer($respondentAnswer);

                    if ($isChoosed) {
                        $variant = $this->em->find(AnswerVariant::class, $answer);
                        $variant->addRespondentAnswer($respondentAnswer);
                    } else {
                        $respondentAnswer->setValue($answer);
                    }

                    $this->em->persist($respondentAnswer);
                }
            }

            $this->em->flush();

            $client->sendMessage(
                $update->getFromId(),
                'Ваша анкета сохранена. Спасибо за участие в опросе!',
                $client->getKeyboardMaker()->newEmptyReplyKeyboard()
            );

            $stateData = (new RespondentStateData(State::SURVEY_CHOOSING))
                ->setRespondent($stateData->getRespondent());

            return $this->sendSurveys($update, $client, $stateData);
        } else if ($update->getMessageText() === 'Отменить участие') {
            $client->sendMessage(
                $update->getFromId(),
                'Вы отменили сохранение анкеты'
            );

            $stateData = (new RespondentStateData(State::SURVEY_CHOOSING))
                ->setRespondent($stateData->getRespondent());

            return $this->sendSurveys($update, $client, $stateData);
        }

        $client->sendMessage($update->getFromId(), 'Выберите действие');

        return $stateData;
    }

    private static function orderAnswers(array $answers): array
    {
        $orderedAnswers = [];
        foreach ($answers['choosed'] as $i => $answerId) {
            $orderedAnswers[$i] = [true, $answerId];
        }
        foreach ($answers['own'] as $i => $ownAnswerText) {
            $orderedAnswers[$i] = [false, $ownAnswerText];
        }

        ksort($orderedAnswers);

        return $orderedAnswers;
    }
}

<?php

namespace App\Service;

class SurveyStates
{
    /** 
     * Начало
     * Показать действия в боте 
     */
    public const SHOW_BOT = 'showBot';

    /** Показать описание бота */
    public const SHOW_BOT_DESCRIPTION = 'showBotDescription';

    /** Показать все опросы */
    public const SHOW_SURVEYS = 'showSurveys';

    /** Выбрать опрос и проверить доступ к нему */
    public const SURVEY_AUTH = 'surveyAuth';

    /** Показать действия для опроса */
    public const SHOW_SURVEY = 'showSurvey';

    /** Показать описание опроса */
    public const SHOW_SURVEY_DESCRIPTION = 'showSurveyDescription';

    /** Начать опрос */
    public const START_SURVEY = 'startSurvey';

    /** Показать вопрос */
    public const SHOW_QUESTION = 'showQuestion';

    /** Проверить ответ */
    public const VALIDATE_ANSWER = 'validateAnswer';

    /** Отменить участие в опросе */
    public const CANCEL_SURVEY = 'cancelSurvey';

    /** Показать действия после всех ответов */
    public const SURVEY_END = 'surveyEnd';

    /** Редактировать ответ */
    public const EDIT_ANSWER = 'editAnswer'; // TODO может и не буду реализовывать

    /** Выбрать вопрос */
    public const CHOOSE_QUESTION = 'chooseQuestion';

    /** Показать ответы */
    public const SHOW_ANSWERS = 'showAnswers';

    /** Отправить анкету */
    public const SEND_FORM = 'sendForm';

    /** Конец */
    public const END = 'end';

    /** Граф переходов между состояниями */
    public const GRAPH = [
        self::SHOW_BOT => [
            self::SHOW_BOT_DESCRIPTION,
            self::SHOW_SURVEYS,
        ],
        self::SHOW_BOT_DESCRIPTION => self::SHOW_BOT,
        self::SHOW_SURVEYS => self::SURVEY_AUTH,
        self::SURVEY_AUTH => [
            self::SHOW_SURVEY,
            self::SHOW_SURVEYS,
        ],
        self::SHOW_SURVEY => [
            self::SHOW_SURVEY_DESCRIPTION,
            self::START_SURVEY,
        ],
        self::START_SURVEY => self::SHOW_QUESTION,
        self::SHOW_QUESTION => [
            self::VALIDATE_ANSWER,
            self::CANCEL_SURVEY,
        ],
        self::VALIDATE_ANSWER => [
            self::SHOW_QUESTION,
            self::SURVEY_END // Если вопросов не осталось
        ],
        self::CANCEL_SURVEY => self::SHOW_BOT,
        self::SURVEY_END => [
            self::SEND_FORM,
            self::SHOW_ANSWERS,
            self::CANCEL_SURVEY,
        ],
        self::SEND_FORM => self::SHOW_BOT,
        self::SHOW_ANSWERS => [
            self::EDIT_ANSWER, // TODO может и не буду реализовывать
            self::SURVEY_END,
        ],
        self::EDIT_ANSWER => [
            self::CHOOSE_QUESTION,
            self::SURVEY_END // Отменить редактирование ответа
        ],
        self::CHOOSE_QUESTION => self::SHOW_QUESTION
    ];

    public static function getInitialState(): string
    {
        return self::SHOW_BOT;
    }
}

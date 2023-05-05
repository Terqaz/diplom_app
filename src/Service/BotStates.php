<?php

namespace App\Service;

class BotStates
{
    public const START = 'start';
    public const PERSONAL_DATA_ENTERING = 'personalDataEntering';
    public const SURVEY_CHOOSING = 'surveyChoosing';
    public const SURVEY_CHOOSED = 'surveyChoosed';
    public const ANSWERING = 'answering';
    public const FORM_COMPLETED = 'formCompleted';
    public const FORM_SAVING = 'formSaving';

    /** Граф переходов между состояниями */
    public const GRAPH = [
        self::START => [
            self::PERSONAL_DATA_ENTERING,
            self::SURVEY_CHOOSING,
        ],
        self::PERSONAL_DATA_ENTERING => [
            self::SURVEY_CHOOSING
        ],
        self::SURVEY_CHOOSING => [
            self::SURVEY_CHOOSED
        ],
        self::SURVEY_CHOOSED => [
            self::SURVEY_CHOOSING,
            self::ANSWERING
        ],
        self::ANSWERING => [
            self::FORM_COMPLETED,
            self::SURVEY_CHOOSING,
        ],
        self::FORM_COMPLETED => [
            self::SURVEY_CHOOSING,
            self::FORM_SAVING
        ]
    ];
}

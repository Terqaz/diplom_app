
export const DEFAULT_QUESTION = {
    elementType: 'question',
    isRequired: false,
    isOwnAnswersPossible: false,
    title: '',
    type: 'choose_one',
    answerValueType: 'string',
    intervalBorders: { 'left': 0, 'right': 0 },
    answerVariants: '',
    maxVariants: 0,
    ownAnswersCount: 0,
};

export const DEFAULT_SUBCONDITION = {
    isEqual: false
};

export const DEFAULT_JUMP = {
    elementType: 'jump',
    toQuestion: '',
    subconditions: [
        structuredClone(DEFAULT_SUBCONDITION)
    ]
};

export const DEFAULT_SOCIAL_NETWORK_CONFIG = {
    connectionId: '',
    accessToken: '',
    isEnabled: false,
};
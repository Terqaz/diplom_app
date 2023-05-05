export const EMAIL_REGEX = /^[\dA-Za-z][.-_\dA-Za-z]+[\dA-Za-z]?@([-\dA-Za-z]+\.){1,2}[-A-Za-z]{2,7}$/;
export const PHONE_NUMBER_REGEX = /^((8|\+7)[\- ]?)?(\(?\d{3}\)?[\- ]?)?[\d\- ]{7,10}$/;

export function randomInt(max) {
    return Math.floor(Math.random() * max);
}

export function answerVariantsList(variants) {
    if (!variants) {
        return [];
    }
    
    return variants.split('\n').filter(v => v.trim().length > 0);
}

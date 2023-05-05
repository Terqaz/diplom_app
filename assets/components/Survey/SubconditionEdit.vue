<template>
    <div class="mb-3">
        <div class="input-group input-group-sm">
            <span class="input-group-text rounded-0 rounded-top-left-2">
                Если ответ на вопрос
            </span>

            <input type="number"
                   :value="questionNumber"
                   @input="$emit('update:questionNumber', Number($event.target.value))"
                   min="1" max="99"
                   class="form-control">

            <input type="checkbox"
                   :checked="isEqual"
                   @change="$emit('update:isEqual', $event.currentTarget.checked)"
                   class="btn-check" :id="id" autocomplete="off">
            <label class="btn btn-sm btn-outline-primary rounded-0 rounded-top-right-2" :for="id">
                {{ isEqual ? 'Равен' : 'Не равен' }}
            </label>
        </div>
        <div class="input-group input-group-sm mb-1">
            <select
                    :value="variantNumber"
                    @input="$emit('update:variantNumber', Number($event.target.value))"
                    class="form-select form-select-sm border-top-0 rounded-0 rounded-bottom">
                <option v-for="(variant, number) in answerVariants"
                        :key="number"
                        :value="number">
                    {{ variant }}
                </option>
            </select>
        </div>
        <ul v-show="errors.length > 0" class="invalid-feedback d-block mb-0">
            <li v-for="message in errors">
                {{ message }}
            </li>
        </ul>
    </div>
</template>

<script>
import { answerVariantsList, randomInt } from '../../js/utils';

const ERRORS = {
    questionIndexInvalid: 0,
    questionMustHasVariants: 1
};

const ERROR_MESSAGES = {
    0: 'Вопрос должен быть до условия перехода',
    1: 'Вопрос должен содержать варианты ответа'
};

export default {
    name: "SubconditionEdit",

    props: {
        jumpIndex: {
            type: Number,
            required: true
        },
        formElements: {
            type: Array,
            required: true
        },

        //v-model
        questionNumber: {
            type: Number,
            default: ''
        },
        isEqual: {
            type: Boolean,
            default: ''
        },
        variantNumber: {
            type: Number,
            default: ''
        }
    },
    emits: [
        'update',
    ],

    data() {
        return {
            answerVariantsList,

            id: 'checkbox' + randomInt(Number.MAX_SAFE_INTEGER),
            errors: {},

            answerVariants: undefined,
            choosedQuestion: undefined
        };
    },

    created() {
        this.setChoosedQuestionAnswerVariants();
    },

    watch: {
        questionNumber(newValue) {
            if (newValue > this.getPrevQuestionIndex()) {
                const error = ERRORS.questionIndexInvalid;
                this.errors[error] = ERROR_MESSAGES[error];
                this.answerVariants = [];
            } else {
                delete this.errors[ERRORS.questionIndexInvalid];
                this.setChoosedQuestionAnswerVariants();
            }
        },

        'choosedQuestion.answerVariants'(newValue) {
            this.setAnswerVariants();
        }
    },

    methods: {
        getPrevQuestionIndex() {
            let i = 0;

            for (; i < this.jumpIndex; i++) {
                if (this.formElements[i].elementType !== 'question') {
                    break;
                }
            }

            return i;
        },

        setChoosedQuestionAnswerVariants() {
            if (!this.questionNumber) {
                return;
            }

            const question = this.formElements.find((element) =>
                element.elementType === 'question'
                && element.number === this.questionNumber
            );

            if (!question) {
                return;
            }

            if (question.variantsPresentType === 'no') {
                const error = ERRORS.questionMustHasVariants;
                this.errors[error] = ERROR_MESSAGES[error];
                return;
            }

            delete this.errors[ERRORS.questionMustHasVariants];

            this.choosedQuestion = question;
            this.setAnswerVariants();
        },

        setAnswerVariants() {
            const answerVariants = {};
            let number = 1;

            this.answerVariantsList(this.choosedQuestion.answerVariants).forEach(variant => {
                answerVariants[number] = variant;
                number++;
            });

            this.answerVariants = answerVariants;
        },

        // update(field, value) {
        //     console.log(field, value);
        //     this.subcondition[field] = value;
        //     this.$emit("update", this.index, field, value);
        // }
    }
}
</script>

<style lang="scss" scoped></style>
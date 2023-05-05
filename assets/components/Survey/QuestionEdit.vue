<template>
    <div class="border p-3 pb-0 rounded-3">
        <DropdownOptions @menu-click="(action) => $emit('menu-click', formIndex, action)" />

        <div class="d-flex flex-row align-items-center mb-3">
            <h5 class="d-inline m-0 me-auto">{{ 'Вопрос №' + number }}</h5>

            <CheckboxButtonField :modelValue="isRequired"
                                 @update:modelValue="$emit('update:isRequired', $event)"
                                 label="Обязательный" />

            <CheckboxButtonField v-if="type !== 'choose_all_ordered'"
                                 v-model="isOwnAnswersPossible"
                                 label="Свои ответы"
                                 class="ms-2" />
        </div>

        <InputField :modelValue="title"
                    @update:modelValue="$emit('update:title', $event)"
                    type="textarea" />

        <SelectField :modelValue="type"
                     @update:modelValue="$emit('update:type', $event)"
                     label="Тип"
                     :options="questionTypes" />

        <SelectField :modelValue="answerValueType"
                     @update:modelValue="$emit('update:answerValueType', $event)"
                     label="Тип ответа"
                     :options="answerValueTypes" />

        <SelectField v-show="!isOwnAnswersPossible || type !== 'choose_one'"
                    :modelValue="variantsPresentType"
                     @update:modelValue="$emit('update:variantsPresentType', $event)"
                     label="Задать варианты ответов"
                     :options="variantsPresentTypes" />

        <InputField v-if="variantsPresentType === 'array'"
                    :modelValue="answerVariants"
                    @update:modelValue="$emit('update:answerVariants', $event)"
                    type="textarea"
                    help="Каждый вариант на новой строке"
                    :errors="errors.answerVariants ?? []" />

        <IntervalField v-else-if="variantsPresentType === 'interval'"
                       :leftValue="intervalBorders.left"
                       @update:leftValue="$emit('update:intervalBorders', 'left', $event)"
                       :rightValue="intervalBorders.right"
                       @update:rightValue="$emit('update:intervalBorders', 'right', $event)"
                       type="text"
                       label="Интервал для числа" />

        <InputField v-show="isManyVariants && type !== 'choose_all_ordered' && variantsPresentType !== 'no'"
                    :modelValue="maxVariants"
                    @update:modelValue="$emit('update:maxVariants', $event)"
                    type="number"
                    label="Максимум выбираемых ответов"
                    :min="minChoosedVariantsCount"
                    :max="variantsPresentType === 'array'
                        ? answerVariantsList(answerVariants).length ?? 0
                        : 10" />

        <InputField v-show="isManyVariants && type !== 'choose_all_ordered' && isOwnAnswersPossible"
                    :modelValue="ownAnswersCount"
                    @update:modelValue="$emit('update:ownAnswersCount', $event)"
                    type="number"
                    label="Максимум собственных ответов"
                    :min="1" max="100" />
    </div>
</template>

<script>
import DropdownOptions from './DropdownOptions.vue';
import CheckboxButtonField from '../Form/CheckboxButtonField.vue';
import InputField from '../Form/InputField.vue';
import IntervalField from '../Form/IntervalField.vue';
import SelectField from '../Form/SelectField.vue';
import { answerVariantsList } from '../../js/utils';

const ERRORS = {
    answerVariants: {
        tooFewVariants: (min) => `Минимальное количество вариантов: ${min}`,
        tooManyVariants: (max) => `Максимальное количество вариантов: ${max}`,
    }
};

export default {
    name: 'QuestionEdit',
    components: { InputField, SelectField, CheckboxButtonField, IntervalField, DropdownOptions, DropdownOptions },
    props: {
        formIndex: {
            type: Number,
            required: true
        },
        number: {
            type: Number,
            required: true
        },

        // v-model
        isRequired: {
            type: Boolean,
            required: true
        },
        title: {
            type: String,
            default: ''
        },
        type: {
            type: String,
            default: 'choose_one'
        },
        answerValueType: {
            type: String,
            default: 'string'
        },
        ownAnswersCount: {
            type: Number,
            default: 0
        },
        variantsPresentType: {
            type: String,
            default: 'no'
        },
        answerVariants: {
            type: String,
            default: ''
        },
        intervalBorders: {
            type: Array
        },
        maxVariants: {
            type: Number,
            default: 1
        },
    },
    events: [
        'menu-click'
    ],

    data() {
        return {
            isOwnAnswersPossible: this.ownAnswersCount > 0,

            questionTypes: undefined,
            answerValueTypes: undefined,
            variantsPresentTypes: undefined,

            answerVariantsList,

            errors: {}
        };
    },

    created() {
        this.questionTypes = JSON.parse(sessionStorage.getItem('questionTypes'));
        this.answerValueTypes = JSON.parse(sessionStorage.getItem('answerValueTypes'));
    },

    watch: {
        type(newValue) {
            if (newValue === 'choose_one') {
                if (this.isOwnAnswersPossible) {
                    this.$emit('update:ownAnswersCount', 1);
                    this.$emit('update:maxVariants', 0);
                } else {
                    this.$emit('update:ownAnswersCount', 0);
                    this.$emit('update:maxVariants', 1);
                }
            }
        },

        isOwnAnswersPossible(newValue) {
            if (newValue) {
                if (this.type === 'choose_one') {
                    this.$emit('update:variantsPresentType', 'no');
                    this.$emit('update:ownAnswersCount', 1);
                    this.$emit('update:maxVariants', 0);
                }
            } else {
                if (this.type === 'choose_one') {
                    this.$emit('update:variantsPresentType', 'array');
                    this.$emit('update:ownAnswersCount', 0);
                    this.$emit('update:maxVariants', 1);
                }
            }
        },

        answerValueType(newValue) {
            if (newValue === 'string' && this.variantsPresentType === 'interval') {
                this.$emit(
                    'update:variantsPresentType',
                    this.isOwnAnswersPossible ? 'no' : 'array'
                );
            }
        },

        answerVariants(newValue) {
            const errors = [];
            const variantsList = this.answerVariantsList(newValue);

            if (variantsList.length < this.minChoosedVariantsCount) {
                errors.push(ERRORS.answerVariants.tooFewVariants(this.minChoosedVariantsCount));
            } else if (variantsList.length > 10) {
                errors.push(ERRORS.answerVariants.tooManyVariants(10));
            }

            this.errors.answerVariants = errors;
        },
    },

    computed: {
        isManyVariants() {
            return this.type === 'choose_many'
                || this.type === 'choose_ordered';
        },

        isNumericAnswerValueType() {
            return this.answerValueType === 'integer'
                || this.answerValueType === 'number';
        },

        variantsPresentTypes() {
            const types = {};

            if (this.isOwnAnswersPossible) {
                // Можно не вводить варианты
                types.no = 'Нет'
            }

            types.array = 'Массивом';

            if (this.isNumericAnswerValueType) {
                types.interval = 'Интервалом'
            }

            return types;
        },

        minChoosedVariantsCount() {
            return !this.isOwnAnswersPossible || this.type === 'choose_ordered' ? 2 : 1;
        }
    },

    methods: {
    }
}
</script>

<style lang="scss" scoped></style>
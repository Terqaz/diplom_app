<template>
    <div class="border p-3 pb-0 rounded-3">
        <DropdownOptions @menu-click="(action) => $emit('menu-click', formIndex, action)"/>

        <div class="mb-3">
            <div class="d-flex flex-row align-items-center">
                <h5 class="d-inline m-0 me-1">Переход на Вопрос №</h5>
                <input :value="toQuestion"
                       @input="$emit('update:toQuestion', Number($event.target.value))"
                       type="number" min="1" max="99"
                       class="to-question-input form-control form-control-sm d-inline">
            </div>
            <p v-show="error" class="invalid-feedback d-block mb-0">{{ error }}</p>
        </div>

        <div v-for="(subcondition, index) in subconditions" :key="subcondition.index">
            <SubconditionEdit :jump-index="formIndex"
                              :form-elements="formElements"

                              :questionNumber="subcondition.questionNumber"
                              @update:questionNumber="$emit('update:subcondition', index, 'questionNumber', $event)"

                              :isEqual="subcondition.isEqual"
                              @update:isEqual="$emit('update:subcondition', index, 'isEqual', $event)"

                              :variantNumber="subcondition.variantNumber"
                              @update:variantNumber="$emit('update:subcondition', index, 'variantNumber', $event)" />
        </div>
    </div>
</template>

<script>
const INVALID_INDEXES_ERROR = 'Вопрос для перехода должен быть после следующего';

import InputField from '../Form/InputField.vue';
import DropdownOptions from './DropdownOptions.vue';
import SubconditionEdit from './SubconditionEdit.vue';

export default {
    name: 'JumpEdit',
    components: { InputField, SubconditionEdit, DropdownOptions },
    props: {
        formIndex: {
            type: Number,
            required: true
        },
        subconditions: {
            type: Array,
            default: []
        },
        formElements: {
            type: Array,
            required: true
        },

        // v-model
        toQuestion: {
            type: Number,
            required: true
        },
    },
    events: [
        'menu-click'
    ],

    data() {
        return {
            error: '',
        };
    },

    watch: {
        toQuestion(newValue) {
            this.error = newValue > this.getNextQuestionIndex() ? '' : INVALID_INDEXES_ERROR;
        }
    },

    methods: {
        randomId() {
            return 'checkbox' + randomInt(Number.MAX_SAFE_INTEGER);
        }
    },

    methods: {
        getNextQuestionIndex() {
            let i = this.formIndex;
            for (; i < this.formElements.length; i++) {
                if (this.formElements[i].elementType === 'question') {
                    break;
                }
            }

            return i;
        },
    }
}
</script>

<style lang="css" scoped>
.to-question-input {
    max-width: 4rem;
}
</style>
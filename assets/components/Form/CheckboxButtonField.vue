<template>
    <span :class="class">
        <input v-model="value"
               type="checkbox"
               class="btn-check"
               :id="id"
               autocomplete="off"
               :disabled="disabled">
        <label class="btn btn-sm btn-outline-primary" :for="id">{{ label }}</label>
    </span>
</template>

<script>

import { randomInt } from '../../js/utils';

export default {
    name: 'CheckboxButtonField',
    props: {
        modelValue: {},
        label: {
            type: String,
            required: true,
        },
        class: {
            type: String,
            required: false
        },
        disabled: {
            type: Boolean,
            default: false
        },
    },
    emits: [
        'update:modelValue',
    ],

    data() {
        return {
            id: 'checkbox' + randomInt(Number.MAX_SAFE_INTEGER)
        };
    },

    computed: {
        value: {
            get() {
                return this.modelValue;
            },
            set(value) {
                this.$emit("update:modelValue", value);
            }
        },
    },
}
</script>

<style scoped></style>
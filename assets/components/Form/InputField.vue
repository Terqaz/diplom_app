<template>
    <div class="mb-3">
        <div class="input-group input-group-sm mb-1">
            <span v-if="label" class="input-group-text">{{ label }}</span>

            <input v-if="type !== 'textarea'"
                   v-model="value"
                   :type="type" 
                   :placeholder="placeholder"
                   :min="min" 
                   :max="max"
                   :disabled="disabled"
                   class="form-control">

            <textarea v-else
                      v-model="value"
                      :type="type" 
                      :placeholder="placeholder"
                      :disabled="disabled"
                      class="form-control"
                      rows="2"></textarea>
        </div>
        <div v-if="help" class="small text-muted mt-2">{{ help }}</div>
        <ul v-show="errors.length > 0" class="invalid-feedback d-block mb-0">
            <li v-for="message in errors">
                {{ message }}
            </li>
        </ul>
    </div>
</template>

<script>
export default {
    name: "InputField",

    props: {
        modelValue: {},

        label: {
            type: String,
            required: false,
        },
        type: {
            type: String,
            default: 'text'
        },
        min: {
            type: Number,
            required: false,
        },
        max: {
            type: Number,
            required: false,
        },
        placeholder: {
            type: String,
            default: ''
        },
        help: {
            type: String,
            default: ''
        },
        disabled: {
            type: Boolean,
            default: false
        },
        errors: {
            type: Array,
            default: []
        },
    },
    emits: [
        'update:modelValue',
    ],

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

<style lang="scss" scoped></style>
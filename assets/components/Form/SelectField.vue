<template>
    <div class="mb-3">
        <div class="input-group input-group-sm mb-1">
            <span class="input-group-text">{{ label }}</span>
            <select v-model="value" :placeholder="placeholder" class="form-select form-select-sm">
                <option v-for="(value, option) in options"
                        :key="option"
                        :value="option">{{ value }}</option>
            </select>
        </div>
        <div class="small text-muted">{{ help }}</div>
    </div>
</template>

<script>
export default {
    name: "SelectField",

    props: {
        modelValue: {},
        options: {
            type: Object,
            required: true
        },
        label: {
            type: String,
            required: true,
        },
        placeholder: {
            type: String,
            default: ''
        },
        help: {
            type: String,
            default: ''
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
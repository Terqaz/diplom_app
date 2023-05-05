<template>
    <div class="mb-3">
        <div class="input-group input-group-sm mb-1">
            <span v-if="label" class="input-group-text">{{ label }}</span>

            <input v-model="leftValueInput"
                   type="number" class="form-control">

            <span class="input-group-text">-</span>

            <input v-model="rightValueInput"
                   type="number" class="form-control">
        </div>
        <div v-if="help" class="small text-muted mt-2">{{ help }}</div>
        <p v-show="error" class="invalid-feedback d-block mb-0">{{ error }}</p>
    </div>
</template>

<script>

const INVALID_VALUES_ERROR = 'Левое значение должно быть не больше правого';

export default {
    name: "IntervalField",

    props: {
        label: {
            type: String,
            required: false,
        },
        help: {
            type: String,
            default: ''
        },

        // v-model
        leftValue: {
            type: Number,
            default: 0
        },
        rightValue: {
            type: Number,
            default: 0
        },
    },
    emits: [
        'update:leftValue',
        'update:rightValue',
    ],

    data() {
        return {
            error: ''
        };
    },

    mounted() {
        this.validate(this.leftValue, this.rightValue);
    },

    computed: {
        leftValueInput: {
            get() {
                return this.leftValue;
            },
            set(value) {
                this.validate(value, this.rightValue);
                this.$emit("update:leftValue", value);
            }
        },
        rightValueInput: {
            get() {
                return this.rightValue;
            },
            set(value) {
                this.validate(this.leftValue, value);
                this.$emit("update:rightValue", value);
            }
        },
    },

    methods: {
        validate(leftValue, rightValue) {
            const isValid = leftValue <= rightValue;
            this.error = isValid ? '' : INVALID_VALUES_ERROR;
            return isValid;
        },
    }
}
</script>

<style lang="scss" scoped></style>
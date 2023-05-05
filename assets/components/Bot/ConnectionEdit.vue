<template>
    <div class="d-flex flex-row align-items-center mb-3">
        <h5 class="d-inline m-0 me-3">{{ socialNetworkCodes[code] }}</h5>

        <CheckboxButtonField :modelValue="isEnabled"
                             @update:modelValue="$emit('update:isEnabled', $event)"
                             label="Включено"
                             class="me-3"
                             :disabled="!connectionId || !accessToken" />

        <button v-show="accessToken && !['inProcess', 'success', 'failed'].includes(connectionTestStatus)"
                @click="testConnection"
                class="btn btn-sm btn-outline-info me-3">
            Проверить подключение
        </button>

        <i v-show="connectionTestStatus === 'inProcess'" class="bi bi-arrow-repeat"></i>
        <i v-show="connectionTestStatus === 'success'" class="bi bi-check text-success"></i>
        <i v-show="connectionTestStatus === 'failed'" class="bi bi-x text-danger"></i>
    </div>

    <InputField :modelValue="connectionId"
                @update:modelValue="$emit('update:connectionId', $event)"
                :label="connectionIdLabels[code]"
                type="text" />

    <InputField :modelValue="accessToken"
                @update:modelValue="$emit('update:accessToken', $event)"
                label="Токен доступа"
                type="password"
                help="Укажите для проверки подключения"
                :disabled="connectionTestStatus === 'inProcess'"
                :errors="testErrorMessage ? [testErrorMessage] : []" />
</template>

<script>
import { ROUTES } from '../../js/routes';
import CheckboxButtonField from '../Form/CheckboxButtonField.vue';
import InputField from '../Form/InputField.vue';

export default {
    name: "ConnectionEdit",
    components: { InputField, CheckboxButtonField },
    props: {
        code: {
            type: String,
            required: true
        },

        // v-model
        connectionId: {
            type: String,
            required: true
        },
        accessToken: {
            type: String,
            required: true
        },
        isEnabled: {
            type: Boolean,
            default: false
        },
    },
    data() {
        return {
            socialNetworkCodes: {},
            connectionIdLabels: {
                tg: 'Название бота',
                vk: 'ID сообщества',
            },

            connectionTestStatus: 'no',
            testErrorMessage: '',

            errors: {}
        }
    },
    created() {
        this.socialNetworkCodes = JSON.parse(sessionStorage.getItem('socialNetworkCodes'));
    },
    watch: {
        accessToken(newValue) {
            this.connectionTestStatus = 'no';
            this.testErrorMessage = '';
        }
    },

    methods: {
        testConnection() {
            this.connectionTestStatus = 'inProcess';

            fetch(ROUTES.app_connection_test(this.code), {
                method: 'POST',
                body: JSON.stringify({ accessToken: this.accessToken }),
                headers: { 'Content-Type': 'application/json' }
            })
                .then((response) => { return response.json(); })
                .then((data) => {
                    if (data.status === false) {
                        this.testErrorMessage = "Не удалось подключиться с данным токеном доступа";
                    }
                    this.connectionTestStatus = data.status ? 'success' : 'failed';
                });
        },
    },
}
</script>

<style lang="css" scoped></style>
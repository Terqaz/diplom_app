<template>
    <FormHeader label="Настройка доступов пользователей"
                :entityType="entityTypeRu"
                :entityName="entityData.title"
                :backUrl="backUrl[entityData.type]" />
    <hr>

    <div class="mb-3">
        <span v-if="savingStatus === 'success'" class="text-success">
            <span class="h5 me-2">Настройки сохранены</span>
            <i class="bi bi-check"></i>
        </span>

        <i v-else-if="savingStatus === 'inProcess'" class="bi bi-arrow-repeat"></i>

        <span v-else-if="savingStatus === 'failed'" class="text-danger">
            <span class="h5 me-2">{{ error }}</span>
            <i class="bi bi-x"></i>
        </span>
    </div>

    <table class="table mb-3">
        <thead>
            <tr>
                <th scope="col">#</th>
                <th scope="col">Имя</th>
                <th scope="col">Email</th>
                <th scope="col">Роль</th>
                <th scope="col">Действия</th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="(access, index) in accesses" :key="access.uid">
                <th scope="row">{{ index + 1 }}</th>
                <td>{{ access.userData.lastAndFirstName }}</td>
                <td>{{ access.userData.email }}</td>
                <td>
                    <select v-model="access.role" class="form-select form-select-sm"
                            @change="postAccess({ index: index, action: 'update' })">
                        <option v-for="(value, option) in typesCatalogs.userRole"
                                :key="option"
                                :value="option">{{ value }}</option>
                    </select>
                </td>
                <td>
                    <button @click="postAccess({ index: index, action: 'remove' })"
                            class="btn btn-sm btn-outline-danger">
                        Удалить
                    </button>
                </td>
            </tr>
            <tr>
                <th scope="row">{{ accesses.length + 1 }}</th>
                <td></td>
                <td>
                    <input v-model="newAccess.email"
                           type="email"
                           class="form-control form-control-sm"
                           placeholder="Введите email">
                </td>
                <td>
                    <select v-model="newAccess.role" class="form-select form-select-sm">
                        <option v-for="(value, option) in typesCatalogs.userRole"
                                :key="option"
                                :value="option">{{ value }}</option>
                    </select>
                </td>
                <td>
                    <button :disabled="!newAccess.email || !newAccess.role"
                            @click="postAccess({ action: 'add' })"
                            type="submit" class="btn btn-sm btn-primary">Добавить</button>
                </td>
            </tr>
        </tbody>
    </table>
    <hr>

    <h5 class="mb-2">Респонденты</h5>

    <div v-if="entityData.type === 'survey'"
         class="small text-muted mb-3">
        Укажите респондентов, которым необходимо изменить доступ
    </div>

    <div class="mb-1">
        <span>
            <CheckboxButtonField v-model="isRespondentsAdd"
                                 :label="isRespondentsAdd ? 'Добавить' : 'Удалить'"
                                 class="me-3" />

            <CheckboxButtonField v-model="isRespondentEmails"
                                 :label="isRespondentEmails ? 'Email адреса' : 'Номера телефонов'"
                                 class="me-3" />

            <span class="small text-muted mt-2">Переключите кнопки для выбора действия</span>
        </span>
    </div>

    <div class="mb-3">
        <div v-if="entityData.type === 'survey'"
             class="small text-muted mb-3">
            <div v-if="isRespondentsAdd">
                Если респонденту не предоставлен доступ в боте, то он будет пропущен
            </div>
        </div>
        <div v-if="entityData.type === 'bot'"
             class="small text-muted mb-3">
            <div v-if="!isRespondentsAdd">
                Указанным респондентам также будет запрещен доступ во всех опросах данного бота
            </div>
        </div>
    </div>

    <InputField v-model="respondentsData"
                help="Каждый респондент на новой строке"
                type="textarea"
                :errors="respondentsDataError ? [respondentsDataError] : []" />

    <span>
        <button :disabled="!respondentsData || respondentsDataError.length > 0"
                @click="postAccess({ action: respondentsAction })"
                type="submit"
                class="btn btn-sm btn-primary me-3">Применить</button>

        <span>
            {{ respondentsActionResultMessage }}
        </span>
    </span>
</template>

<script>
import CheckboxButtonField from './Form/CheckboxButtonField.vue'
import { ROUTES } from '../js/routes';
import { EMAIL_REGEX, PHONE_NUMBER_REGEX, randomInt } from '../js/utils';
import InputField from './Form/InputField.vue';
import FormHeader from './Form/FormHeader.vue';

const ERRORS = {
    userAlreadyAdded: 'Пользователю с данным email уже дана роль',
    userNotFound: 'Пользователь с указанным email адресом не найден',
    addFailed: 'Не удалось добавить пользователя',
    removeFailed: 'Не удалось удалить пользователя',
    respondentsDataIncorrect: 'Проверьте правильность ввода данных',
    serviceUnavailable: 'Сервис временно недоступен'
};

export default {
    name: "UserAccessesEdit",
    components: { FormHeader, InputField, CheckboxButtonField },
    props: {
        entityData: {
            type: Object,
            required: true
        },
        typesCatalogs: {
            type: Array,
            required: true
        }
    },

    data() {
        return {
            accesses: structuredClone(this.entityData.users),
            backUrl: {
                bot: ROUTES.app_bot_show(this.entityData.id),
                survey: ROUTES.app_survey_show(this.entityData.id),
            },

            routes: {
                accessChange: {
                    bot: ROUTES.app_bot_user_access_change,
                    survey: ROUTES.app_survey_user_access_change
                }
            },

            newAccess: {
                email: '',
                role: ''
            },

            isRespondentsAdd: true,
            isRespondentEmails: true,
            respondentsData: '',

            savingStatus: 'success',
            error: '',

            respondentsDataError: '',
            respondentsActionResultMessage: ''
        };
    },

    created() {
        this.accesses.forEach(access => {
            access.uid = randomInt(Number.MAX_SAFE_INTEGER);
        });
    },

    watch: {
        'newAccess.email'(newValue, oldValue) {
            for (const access of this.accesses) {
                if (access.userData.email === newValue) {
                    this.error = ERRORS.userAlreadyAdded;
                    this.savingStatus = 'failed';
                    return;
                }
            }

            this.error = '';
            this.savingStatus = 'success';
        },

        respondentsData(newValue, oldValue) {
            this.respondentsDataError = '';
        },

        isRespondentEmails(newValue, oldValue) {
            this.respondentsDataError = '';
        },
    },

    computed: {
        entityTypeRu() {
            return {
                bot: 'Бот',
                survey: 'Опрос',
            }[this.entityData.type];
        },

        respondentsAction() {
            let action = 'respondents';

            action += this.isRespondentsAdd ? '-add' : '-remove';
            action += this.isRespondentEmails ? '-emails' : '-phones';

            return action;
        },

        respondentsDataList() {
            return this.respondentsData.split('\n')
                .filter(r => r.length > 0)
                .map(r => r.trim());
        }
    },

    methods: {
        postAccess({ index, action } = {}) {
            this.error = '';
            this.savingStatus = 'inProcess';

            let body;

            if (action === 'add') {
                body = structuredClone(this.newAccess);
            } else if (action === 'update') {
                body = {
                    id: this.accesses[index].id,
                    newRole: this.accesses[index].role
                };
            } else if (action === 'remove') {
                body = {
                    id: this.accesses[index].id
                };
            } else if (action.startsWith('respondent')) {
                this.respondentsDataError = '';
                this.respondentsActionResultMessage = '';

                const respondentsDataList = this.respondentsDataList;

                if (!this.respondentsDataCorrect(respondentsDataList)) {
                    this.respondentsDataError = ERRORS.respondentsDataIncorrect;

                    // Настройки по прежнему синхронизированы
                    this.error = '';
                    this.savingStatus = 'success';

                    return;
                }

                body = {
                    data: respondentsDataList
                };
            }

            let route = this.routes.accessChange[this.entityData.type];

            fetch(route(this.entityData.id, action), {
                method: 'POST',
                body: JSON.stringify(body),
                headers: { 'Content-Type': 'application/json' }
            })
                .then(response => {
                    if (response.status === 404) {
                        throw new Error('userNotFound');
                    }

                    return response.json()
                })
                .then(data => {
                    if (!data.status) {
                        throw new Error(action + 'Failed');
                    }

                    this.error = '';
                    this.savingStatus = 'success';

                    if (action === 'add') {
                        this.addAccess(data);
                    } else if (action === 'remove') {
                        this.removeAccess(index);
                    } else if (action.startsWith('respondent')) {
                        let message = this.isRespondentsAdd
                            ? 'Добавлено '
                            : 'Удалено ';

                        message += 'респондентов: ' + data.changedCount;

                        this.respondentsActionResultMessage = message;
                    }
                })
                .catch((error => {
                    const errorMessage = ERRORS[error.message];
                    if (errorMessage) {
                        this.error = errorMessage;
                    } else {
                        this.error = ERRORS.serviceUnavailable;
                    }
                    this.savingStatus = 'failed';
                    console.log(error);
                }).bind(this));
        },

        addAccess(data) {
            const newAccess = {
                id: data.newId,
                role: this.newAccess.role,
                userData: {
                    lastAndFirstName: data.user.name,
                    email: this.newAccess.email,
                },
                uid: randomInt(Number.MAX_SAFE_INTEGER),
            };

            this.accesses.push(newAccess);

            this.newAccess.role = '';
            this.newAccess.email = '';
        },

        removeAccess(index) {
            this.accesses.splice(index, 1);
        },

        respondentsDataCorrect(respondentsDataList) {
            const regex = this.isRespondentEmails
                ? EMAIL_REGEX
                : PHONE_NUMBER_REGEX;

            for (const value of respondentsDataList) {
                if (!regex.test(value)) {
                    return false;
                }
            }

            return true;
        }
    },
}
</script>

<style lang="scss" scoped></style>
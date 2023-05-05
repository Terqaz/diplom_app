<template>
    <div class="mx-auto" style="max-width: 700px">
        <FormHeader label="Настройка повторного прохождения" entityType="Опрос"
                    :entityName="survey.title" :backUrl="backUrl" />
        <hr>

        <div class="form-check mb-3">
            <input v-model="survey.isMultiple"
                   type="checkbox" class="form-check-input">
            <label class="form-label mb-0">Многоразовый опрос</label>
            <div class="small text-muted">
                {{ survey.isMultiple
                    ? 'Не разбивается на итерации. Можно проходить несколько раз в любое время'
                    : 'Разбивается на итерации. Можно пройти только один раз после начала итерации' }}
            </div>
        </div>

        <div v-show="!survey.isMultiple">
            <div class="form-check mb-3">
                <input v-model="isScheduled"
                       type="checkbox" class="form-check-input">
                <label class="form-label mb-0">По расписанию</label>
                <div class="small text-muted">
                    Будет доступен для одного прохождения только после указанных моментов времени
                </div>
            </div>

            <div v-show="isScheduled">
                <SelectField v-model="survey.schedule.type"
                             label="Частота повторения"
                             :options="typesCatalogs.scheduleTypes" />

                <div class="mb-3">
                    <div class="input-group input-group-sm mb-1">
                        <template v-if="survey.schedule.type === 'during_day'">
                            <span class="input-group-text">Список часов и минут дня</span>
                            <input type="text" class="form-control"
                                   v-model="repeatValues.times">
                        </template>
                        <template v-else-if="['during_week', 'during_month'].includes(survey.schedule.type)">
                            <span class="input-group-text">Дни для проведения</span>
                            <input type="text" class="form-control"
                                   v-model="repeatValues.days">

                            <span class="input-group-text">Время дня</span>
                            <input type="text" class="form-control"
                                   v-model="repeatValues.times">
                        </template>
                        <template v-else-if="survey.schedule.type === 'during_year'">
                            <span class="input-group-text">Месяцы для проведения</span>
                            <input type="text" class="form-control"
                                   v-model="repeatValues.months">

                            <span class="input-group-text">День месяца</span>
                            <input type="text" class="form-control"
                                   v-model="repeatValues.days" min="1" max="31">

                            <span class="input-group-text">Время дня</span>
                            <input type="text" class="form-control"
                                   v-model="repeatValues.times">
                        </template>
                    </div>
                </div>

                <div class="form-check mb-3">
                    <input v-model="survey.schedule.isOnce"
                           type="checkbox" class="form-check-input">
                    <label class="form-label mb-0">Одноразовый опрос</label>
                    <div class="small text-muted">
                        Будет проведена только одна итерация опроса
                    </div>
                </div>

                <div class="form-check mb-3">
                    <input v-model="survey.schedule.isNoticeOnStart"
                           type="checkbox" class="form-check-input">
                    <label class="form-label mb-0">Уведомлять при старте</label>
                </div>

                <SelectField v-model="survey.schedule.noticeBefore"
                             label="Уведомить до начала за"
                             :options="typesCatalogs.scheduleNoticeBeforeTypes" />
            </div>
        </div>

        <span>
            <button @click="submit" class="btn btn-sm btn-primary me-3">
                Сохранить
            </button>

            <span v-if="savingStatus === 'success'" class="text-success">
                <span class="me-2">Настройки сохранены</span>
                <i class="bi bi-check"></i>
            </span>

            <i v-else-if="savingStatus === 'inProcess'" class="bi bi-arrow-repeat"></i>

            <span v-else-if="savingStatus === 'failed'" class="text-danger">
                <span class="me-2">{{ error }}</span>
                <i class="bi bi-x"></i>
            </span>
        </span>


    </div>
</template>

<script>
import { ROUTES } from '../../js/routes';
import CheckboxButtonField from '../Form/CheckboxButtonField.vue';
import FormHeader from '../Form/FormHeader.vue';
import SelectField from '../Form/SelectField.vue';

const ERRORS = {
    savingFailed: 'Не удалось сохранить настройки',
    serviceUnavailable: 'Сервис временно недоступен'
};

export default {
    name: 'SurveyScheduleEdit',
    components: { CheckboxButtonField, FormHeader, SelectField },
    props: {
        surveyData: {
            type: Object,
            required: true
        },
        typesCatalogs: {
            type: Array,
            required: true
        }
    },

    data() {
        const survey = structuredClone(this.surveyData);

        const isScheduled = !!survey.schedule;
        let repeatValues = {};

        if (!isScheduled) {
            survey.schedule = {
                type: 'during_day',
                isOnce: true,
                isNoticeOnStart: false
            };
        } else {
            let repeatValuesData = JSON.parse(survey.schedule.repeatValues);

            if (survey.schedule.type === 'during_day') {
                repeatValues.times = repeatValuesData.join(', ');
            } else if (['during_week', 'during_month'].includes(survey.schedule.type)) {
                repeatValues.days = repeatValuesData[0].join(', ');
                repeatValues.times = repeatValuesData[1].toString();
            } else if (survey.schedule.type === 'during_year') {
                repeatValues.months = repeatValuesData[0].join(', ');
                repeatValues.days = repeatValuesData[1].toString();
                repeatValues.times = repeatValuesData[2].toString();
            }
        }

        return {
            backUrl: ROUTES.app_survey_show(survey.id),

            survey,
            isScheduled,
            repeatValues,

            savingStatus: 'no',
            error: ''
        }
    },

    created() {
    },

    methods: {
        submit() {
            this.error = '';
            this.savingStatus = 'inProcess';

            const body = {
                isMultiple: this.survey.isMultiple
            };

            if (this.isScheduled && !this.survey.isMultiple) {
                const schedule = structuredClone(this.survey.schedule);

                let repeatValues;
                if (this.survey.schedule.type === 'during_day') {
                    repeatValues = this.toArrayValues(this.repeatValues.times);
                } else if (['during_week', 'during_month'].includes(this.survey.schedule.type)) {
                    repeatValues = [
                        this.toArrayValues(this.repeatValues.days)
                            .map(value => +value),
                        this.repeatValues.times
                    ];
                } else if (this.survey.schedule.type === 'during_year') {
                    repeatValues = [
                        this.toArrayValues(this.repeatValues.months)
                            .map(value => +value),
                        this.repeatValues.days,
                        this.repeatValues.times
                    ];
                }

                schedule.repeatValues = JSON.stringify(repeatValues);
                schedule.noticeBefore = +schedule.noticeBefore;
                body.schedule = schedule;
            }

            fetch(ROUTES.app_survey_schedule_edit(this.surveyData.id), {
                method: 'POST',
                body: JSON.stringify(body),
                headers: { 'Content-Type': 'application/json' }
            })
                .then(response => {
                    if (response.redirected) {
                        window.location.href = response.url;
                        return;
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

        toArrayValues(values) {
            return values.split(',')
                .map(value => value.trim());
        }
    },
}
</script>

<style lang="scss" scoped></style>
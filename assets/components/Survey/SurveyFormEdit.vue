<template>
    <FormHeader label="Настройка анкеты" entityType="Опрос"
                :entityName="survey.title" :backUrl="backUrl" />

    <hr>
    <div class="d-flex flex-row mb-5">
        <div class="elements-edit d-flex flex-column pt-0 border-end pe-3">
            <div class="d-flex flex-row align-items-center mb-1 me-auto">
                <!-- todo валидация -->
                <button @click="submit" class="btn btn-sm btn-primary me-2">
                    Сохранить анкету
                </button>

                <i v-show="savingStatus === 'inProcess'" class="bi bi-arrow-repeat"></i>
                <i v-show="savingStatus === 'success'" class="bi bi-check text-success"></i>
                <i v-show="savingStatus === 'failed'" class="bi bi-x text-danger"></i>
            </div>

            <p v-show="savingStatus === 'failed'" class="text-danger mb-1">
                {{ savingError }}
            </p>

            <div class="border p-3 pb-0 rounded-3 mt-2 mb-3">
                <h5 class="m-0 mb-3">Требуемые данные</h5>
                <div class="d-flex flex-row align-items-center mb-3">
                    <CheckboxButtonField v-model="survey.isEmailRequired"
                                         label="Email"
                                         class="me-2" />

                    <CheckboxButtonField v-model="survey.isPhoneRequired"
                                         label="Номер телефона" />
                </div>
            </div>

            <span v-if="survey.formElements.length > 0" class="px-2">
                <i class="bi bi-info-circle"></i>
                <p class="d-inline m-0 text-black-50">
                    Вы можете добавить новые элементы наведя на края существующих
                </p>
            </span>

            <AddElementButtons :index="0"
                               @click="insertNewElement"
                               :always-show="survey.formElements.length === 0" />

            <div v-for="element in survey.formElements" :key="element.id">
                <QuestionEdit v-if="element.elementType === elementTypes.question"
                              :formIndex="element.formIndex"
                              :number="element.number"
                              v-model:isRequired="element.isRequired"
                              v-model:title="element.title"
                              v-model:type="element.type"
                              v-model:answerValueType="element.answerValueType"
                              v-model:ownAnswersCount="element.ownAnswersCount"

                              v-model:variantsPresentType="element.variantsPresentType"
                              v-model:answerVariants="element.answerVariants"

                              :intervalBorders="element.intervalBorders"
                              @update:intervalBorders="(field, value) => element.intervalBorders[field] = value"

                              v-model:maxVariants="element.maxVariants"
                              @menu-click="applyMenuAction" />

                <JumpEdit v-if="element.elementType === elementTypes.jump"
                          :formIndex="element.formIndex"
                          :subconditions="element.subconditions"
                          :form-elements="survey.formElements"
                          v-model:toQuestion="element.toQuestion"
                          @update:subcondition="(index, field, value) => element.subconditions[index][field] = value"
                          @menu-click="applyMenuAction" />

                <AddElementButtons :index="element.formIndex + 1" @click="insertNewElement" />
            </div>
        </div>
        <DialogPreview :survey="survey" />
    </div>
</template>

<script>
import AddElementButtons from './AddElementButtons.vue';
import JumpEdit from './JumpEdit.vue';
import QuestionEdit from './QuestionEdit.vue';
import DialogPreview from './DialogPreview.vue';
import FormHeader from '../Form/FormHeader.vue';

import { ROUTES } from '../../js/routes'
import { DEFAULT_QUESTION, DEFAULT_JUMP } from '../../js/defaultEntities';
import { answerVariantsList, randomInt } from '../../js/utils';
import CheckboxButtonField from '../Form/CheckboxButtonField.vue';

export default {
    name: "SurveyFormEdit",
    components: { QuestionEdit, JumpEdit, AddElementButtons, DialogPreview, FormHeader, CheckboxButtonField },
    props: {
        surveyData: {
            type: Array,
            required: true
        },
        typesCatalogs: {
            type: Array,
            required: true
        }
    },

    data() {
        return {
            survey: structuredClone(this.surveyData),
            backUrl: ROUTES.app_survey_show(this.surveyData.id),

            elementTypes: {
                question: 'question',
                jump: 'jump',
            },

            savingStatus: 'no',
            savingError: ''
        };
    },

    created() {
        sessionStorage.setItem('questionTypes', JSON.stringify(this.typesCatalogs.question));
        sessionStorage.setItem('answerValueTypes', JSON.stringify(this.typesCatalogs.answerValue));

        let formIndex = 0; // Среди всех элементов анкеты

        let questionNumber = 1;
        let jumpNumber = 1;

        this.survey.formElements.forEach((element) => {
            element.formIndex = formIndex++;
            element.id = randomInt(Number.MAX_SAFE_INTEGER);

            if (element.elementType === this.elementTypes.question) {
                element.number = questionNumber++;

                element.answerVariants = element.answerVariants.join('\n');
                if (!element.intervalBorders) {
                    element.intervalBorders = { 'left': 0, 'right': 0 };
                }

                let variantsPresentType = 'no';

                if (element.answerVariants) {
                    variantsPresentType = 'array';
                } else if (element.intervalBorderLeft && this.intervalBorderRight) {
                    variantsPresentType = 'interval';
                }

                element.variantsPresentType = variantsPresentType;

            } else if (element.elementType === this.elementTypes.jump) {
                element.number = jumpNumber++;

                let subconditionIndex = 0;
                element.subconditions
                    .forEach(subcondition => subcondition.index = subconditionIndex++);
            }
        });
    },

    methods: {
        elementUpdated(formIndex, field, value) {
            this.survey.formElements[formIndex][field] = value;
        },

        insertNewElement(index, elementType) {
            if (elementType === 'question') {
                this.insertNewQuestion(index);
            } else if (elementType === 'jump') {
                this.insertNewJump(index);
            }

            this.changeElementsIndexes(index, elementType, 1);
        },

        insertNewQuestion(index) {
            const newQuestion = structuredClone(DEFAULT_QUESTION);
            newQuestion.id = randomInt(Number.MAX_SAFE_INTEGER);
            newQuestion.formIndex = index;
            newQuestion.number = 1 + this.countElementsBefore(index, 'question');

            this.survey.formElements.splice(index, 0, newQuestion);

            this.changeJumpQuestionsIndexes(index, 1);
        },

        insertNewJump(index) {
            const newJump = structuredClone(DEFAULT_JUMP);
            newJump.id = randomInt(Number.MAX_SAFE_INTEGER);
            newJump.formIndex = index;
            newJump.number = 1 + this.countElementsBefore(index, 'jump');

            this.survey.formElements.splice(index, 0, newJump);
        },

        applyMenuAction(index, action) {
            console.log(index);
            const element = this.survey.formElements[index];

            if (action === 'delete') {
                if (element.elementType === 'question') {
                    this.changeJumpQuestionsIndexes(index, -1);
                }

                this.changeElementsIndexes(index, element.elementType, -1)

                this.survey.formElements.splice(index, 1);
            }
        },

        changeElementsIndexes(fromIndex, elementType, diff) {
            const formElements = this.survey.formElements;

            for (let i = fromIndex + 1; i < formElements.length; i++) {
                const e = formElements[i];
                e.formIndex += diff;

                if (e.elementType === elementType) {
                    e.number += diff;
                }
            }
        },

        changeJumpQuestionsIndexes(questionIndex, diff) {
            this.survey.formElements
                .filter(e => e.elementType === 'jump')
                .forEach((jump) => {
                    if (questionIndex < jump.toQuestion) {
                        jump.toQuestion += diff;
                    }

                    jump.subconditions
                        .forEach(s => {
                            if (questionIndex <= s.questionNumber) {
                                s.questionNumber += diff
                            }
                        });
                });
        },

        countElementsBefore(index, element) {
            let count = 0;
            for (let i = index - 1; i >= 0; i--) {
                if (this.survey.formElements[i].elementType === element) {
                    ++count;
                }
            }

            return count;
        },

        submit() {
            this.savingStatus = 'inProcess';
            this.savingError = '';

            const body = {
                isEmailRequired: this.survey.isEmailRequired,
                isPhoneRequired: this.survey.isPhoneRequired,
                questions: [],
                jumpConditions: [],
            };

            for (const element of this.survey.formElements) {
                const bodyElement = structuredClone(element);

                delete bodyElement.id;
                delete bodyElement.number;

                bodyElement.serialNumber = bodyElement.formIndex + 1;
                delete bodyElement.formIndex;

                if (bodyElement.elementType === 'question') {
                    delete bodyElement.elementType;

                    if (bodyElement.variantsPresentType === 'array') {
                        delete bodyElement.intervalBorders;

                        let number = 1;
                        bodyElement.variants = answerVariantsList(bodyElement.answerVariants)
                            .map(v => ({
                                serialNumber: number++,
                                value: v
                            }));

                        delete bodyElement.answerVariants;
                    } else if (bodyElement.variantsPresentType === 'interval') {
                        delete bodyElement.answerVariants;

                        bodyElement.intervalBorders =
                            bodyElement.intervalBorders.left + '-' + bodyElement.intervalBorders.right;

                        delete bodyElement.intervalBorders;
                    } else {
                        delete bodyElement.intervalBorders;
                        delete bodyElement.answerVariants;
                    }
                    delete bodyElement.variantsPresentType;

                    if (bodyElement.type === 'choose_all_ordered') {
                        bodyElement.maxVariants = bodyElement.variants.length;
                        bodyElement.ownAnswersCount = 0;
                    }

                    body.questions.push(bodyElement);
                } else if (bodyElement.elementType === 'jump') {
                    delete bodyElement.elementType;
                    for (const subcondition of bodyElement.subconditions) {
                        delete subcondition.index;
                        subcondition.variantNumber;
                    }

                    body.jumpConditions.push(bodyElement);
                }
            }

            fetch(ROUTES.app_survey_form_edit(this.survey.id), {
                method: 'POST',
                body: JSON.stringify(body),
                headers: { 'Content-Type': 'application/json' }
            })
                .then(response => response.json())
                .then(data => {
                    this.savingError = data.status ? '' : 'Не удалось сохранить анкету';
                    this.savingStatus = data.status ? 'success' : 'failed';
                })
                .catch((error => {
                    this.savingError = 'Не удалось сохранить анкету';
                    this.savingStatus = 'failed';
                    console.log(error);
                }).bind(this));
        }
    }
}
</script>

<style scoped>
.elements-edit {
    min-width: 25rem;
    max-width: 30rem;
}
</style>
<template>
    <div class="w-100 p-3 pe-0">
        <div class="dialog d-flex flex-column  mx-auto">
            <div v-for="element in survey.formElements" :key="element.id">
                <div v-if="element.elementType === 'question'"
                     class="d-flex flex-column w-100">
                    <div
                         class="element-content">
                        <div class="border border-primary text-primary rounded-3 px-2 py-1 mb-2 me-auto">
                            <div class="mb-3">{{ formatQuestion(element) }}</div>
                            <div>
                                <div v-show="element.variantsPresentType === 'array'"
                                     v-for="(variant, index) in answerVariantsList(element.answerVariants)" :key="variant">
                                    {{ (index + 1) + '. ' + variant }}
                                </div>
                            </div>
                        </div>
                        <div v-show="element.variantsPresentType === 'array'"
                             class="d-flex flex-row flex-wrap mb-3">
                            <div v-for="(variant, index) in answerVariantsList(element.answerVariants)" :key="variant"
                                 class="border border-primary text-primary rounded-3 px-3 py-1 me-1">
                                {{ index + 1 }}
                            </div>
                        </div>
                    </div>
                    <div class="element-content bg-primary text-white rounded-3 px-2 py-1 ms-auto mb-3">
                        Следующий вопрос
                    </div>
                </div>
                <div v-if="element.elementType === 'jump'">
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { answerVariantsList } from '../../js/utils';

export default {
    name: 'DialogPreview',
    props: {
        survey: {
            type: Object,
            required: true
        },
    },

    data() {
        return {
            answerVariantsList,

            questionTypes: undefined,
            answerValueTypes: undefined,
        }
    },

    created() {
        this.questionTypes = JSON.parse(sessionStorage.getItem('questionTypes'));
        this.answerValueTypes = JSON.parse(sessionStorage.getItem('answerValueTypes'));
    },

    methods: {
        formatQuestion(question) {
            let help = [];

            if (!question.isRequired) {
                help.push('необязательный');
            }

            if (question.type === 'choose_one') {
                help.push('один ответ');
            } else if (this.isManyVariants(question.type)) {
                let manyVariantsHelp = [];

                if (question.maxVariants === 1) {
                    manyVariantsHelp.push('один выбираемый ответ')
                } else if (question.maxVariants >= 2) {
                    manyVariantsHelp.push(`до ${question.maxVariants} выбираемых ответов`)
                }

                if (question.ownAnswersCount === 1) {
                    manyVariantsHelp.push('один ваш ответ')
                } else if (question.ownAnswersCount >= 2) {
                    manyVariantsHelp.push(`до ${question.ownAnswersCount} ваших ответов`)
                }

                if (manyVariantsHelp.length > 0) {
                    help.push(manyVariantsHelp.join(' и/или '));
                }
            } else if (question.type === 'choose_all_ordered') {
                help.push('расположите все ответы по порядку');
            }

            if (question.variantsPresentType === 'interval') {
                help.push(`число от ${question.intervalBorders.left} до ${question.intervalBorders.right}`);
            }

            return `Вопрос ${question.number}. ${question.title} (${help.join(', ')})`;
        },

        isManyVariants(questionType) {
            return questionType === 'choose_many'
                || questionType === 'choose_ordered'
        }
    },
}
</script>

<style lang="css" scoped>
.dialog {
    min-width: 20rem;
    max-width: 50rem;
}

.element-content {
    max-width: 40rem;
}
</style>
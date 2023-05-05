<template>
    <div @mouseover="showAddElementButtons(true)"
         @mouseleave="showAddElementButtons(false)">
         <div v-if="show || alwaysShow" class="btn-group btn-group-sm w-100 my-2" role="group">
            <button @click="$emit('click', index, 'question')"
                    class="btn btn-outline-primary">Новый вопрос</button>

            <button @click="$emit('click', index, 'jump')"
                    class="btn btn-outline-primary">Новый переход</button>
        </div>
        <div v-else
             class="add-element-buttons__mouse-handler"></div>
    </div>
</template>

<script>
export default {
    name: 'AddElementButtons',
    props: {
        index: {
            type: Number,
            require: true
        },
        alwaysShow: {
            type: Boolean,
            default: false
        }
    },
    emits: [
        'click'
    ],

    data() {
        return {
            show: false
        };
    },

    methods: {
        showAddElementButtons(isShow) {
            this.show = isShow;
        }
    }
}
</script>

<style scoped>
.add-element-buttons__mouse-handler {
    height: 1rem;
}
</style>
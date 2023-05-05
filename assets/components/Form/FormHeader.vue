<template>
    <div class="container">
        <h5 class="mx-auto text-center">{{ label }}</h5>
        <p class="small text-center">{{ entityType }}
            <a :href="backUrl" class="text-decoration-none">
                {{ entityName }}
            </a>
        </p>
    </div>
</template>

<script>
export default {
    name: 'FormHeader',
    props: {
        label: {
            type: String,
            required: true
        },
        entityType: {
            type: String,
            required: true
        },
        entityName: {
            type: String,
            required: true
        },
        backUrl: {
            type: String,
            required: true
        }
    },
}
</script>

<style lang="scss" scoped></style>
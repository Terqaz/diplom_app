<template>
    <ul class="nav nav-pills flex-column">
        <li v-for="anchor in anchors" :key="anchor.id" class="nav-item">
            <a :href="'#' + anchor.id" class="nav-link link-dark">
                {{ anchor.title }}
            </a>
        </li>
    </ul>
</template>

<script>
export default {
    name: 'MenuAnchors',
    data() {
        return {
            anchors: []
        };
    },
    mounted() {
        document.querySelectorAll('.info-block > h5')
            .forEach((header) => this.anchors.push({
                'id': header.id, 'title': header.innerHTML
            }));
    }
}
</script>

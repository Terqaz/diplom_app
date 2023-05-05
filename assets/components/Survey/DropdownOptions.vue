<template>
    <div class="dropdown">
        <a @click="showMenu = !showMenu" type="button" aria-expanded="false">
            <i class="bi bi-three-dots"></i>
        </a>
        <ul class="dropdown-menu dropdown-menu-sm border-danger" :class="{ 'd-block': showMenu }"
            @mouseleave="showMenu = false">
            <li>
                <button @click="$emit('menu-click', 'delete')"
                        class="dropdown-item text-danger" href="#">Удалить</button>
            </li>
        </ul>
    </div>
</template>

<script>
export default {
    name: 'DropdownOptions',
    data() {
        return {
            showMenu: false
        };
    }
}
</script>

<style lang="css" scoped></style>
<script setup>
import { ref } from 'vue';
import AppMenuItem from './AppMenuItem.vue';

const model = ref([
    {
        label: 'Home',
        items: [
            {
                label: 'Dashboard',
                icon: 'pi pi-fw pi-home',
                href: '/dashboard',
            },
            {
                label: 'Profile',
                icon: 'pi pi-fw pi-user-edit',
                href: '/profile',
            },
        ],
    },
    {
        label: 'Appointment',
        items: [
            {
                label: 'Patient Appointment Master',
                icon: 'pi pi-fw pi-book',
                href: '/dental-patient-appointments',
            },
            {
                label: 'Clinic Appointment',
                icon: 'pi pi-fw pi-book',
                href: '/appointment/clinic',
            },
        ],
    },
]);
</script>

<template>
    <ul class="layout-menu">
        <template v-for="(item, index) in model" :key="item.label">
            <AppMenuItem v-if="!item.separator" :item="item" :index="index" />
            <li v-else class="menu-separator"></li>
        </template>
    </ul>
</template>

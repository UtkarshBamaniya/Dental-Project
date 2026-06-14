<script setup>
import Button from 'primevue/button';

defineProps({
    canCreate: {
        type: Boolean,
        default: true,
    },
    label: {
        type: String,
        default: '',
    },
    iconOnly: {
        type: Boolean,
        default: true,
    },
    tooltip: {
        type: String,
        default: 'Add New',
    },
});
</script>

<template>
    <Button
        v-if="canCreate"
        :label="label"
        icon="pi pi-plus"
        :title="tooltip"
        :class="iconOnly
            ? 'h-[2.75rem] w-[2.75rem] rounded-xl bg-blue-500 !px-0 !py-0 text-white shadow-none hover:bg-blue-600'
            : 'rounded-xl bg-blue-500 px-4 py-2.5 text-white shadow-none hover:bg-blue-600'"
    />
</template>

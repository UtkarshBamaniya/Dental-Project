<script setup>
import PrimeToolbar from 'primevue/toolbar';

defineProps({
    title: {
        type: String,
        required: true,
    },
    desc: {
        type: String,
        default: '',
    },
    icon: {
        type: String,
        default: 'pi pi-briefcase',
    },
});
</script>

<template>
    <PrimeToolbar class="mb-4 !rounded-xl">
        <template #start>
            <div class="flex items-center gap-3">
                <div class="flex h-[2.5rem] w-[2.5rem] flex-shrink-0 items-center justify-center rounded-lg bg-primary text-white shadow-lg shadow-blue-900/20 ring-1 ring-blue-400/30 dark:bg-blue-500">
                    <i :class="[icon, 'text-xl']"></i>
                </div>
                <div>
                    <h1 class="text-xl font-bold tracking-tight text-slate-800 dark:text-white">
                        {{ title }}
                    </h1>
                    <p v-if="desc" class="text-xs text-slate-500 dark:text-slate-400">
                        {{ desc }}
                    </p>
                </div>
            </div>
        </template>

        <template #end>
            <div class="flex flex-wrap items-center gap-2">
                <slot name="actions" />
            </div>
        </template>
    </PrimeToolbar>
</template>

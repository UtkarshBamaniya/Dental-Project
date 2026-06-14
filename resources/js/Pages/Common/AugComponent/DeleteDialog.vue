<script setup>
import { router } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import { ref } from 'vue';

const emit = defineEmits(['fetch-data']);

defineProps({
    moduleName: {
        type: String,
        default: 'record',
    },
});

const visible = ref(false);
const recordId = ref(null);
const routeName = ref('');
const loading = ref(false);

const openDelete = (id, destroyRouteName) => {
    recordId.value = id;
    routeName.value = destroyRouteName;
    visible.value = true;
};

const closeDialog = () => {
    visible.value = false;
    recordId.value = null;
    routeName.value = '';
};

const deleteRecord = () => {
    if (!recordId.value || !routeName.value) {
        return;
    }

    loading.value = true;

    router.delete(route(routeName.value, recordId.value), {
        preserveScroll: true,
        onSuccess: () => {
            closeDialog();
            emit('fetch-data');
        },
        onFinish: () => {
            loading.value = false;
        },
    });
};

defineExpose({
    openDelete,
});
</script>

<template>
    <Dialog
        v-model:visible="visible"
        modal
        header="Confirm Delete"
        :style="{ width: 'min(460px, 92vw)' }"
    >
        <p class="text-sm leading-6 text-slate-600">
            Delete this {{ moduleName }}? This action cannot be undone.
        </p>

        <template #footer>
            <div class="flex justify-end gap-3">
                <Button label="Cancel" severity="secondary" outlined @click="closeDialog" />
                <Button label="Delete" severity="danger" :loading="loading" @click="deleteRecord" />
            </div>
        </template>
    </Dialog>
</template>

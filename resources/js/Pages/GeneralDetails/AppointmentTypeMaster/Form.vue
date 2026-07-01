<script setup>
import { useForm } from '@inertiajs/vue3';
import axios from 'axios';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import Textarea from 'primevue/textarea';
import { computed, ref } from 'vue';

const emit = defineEmits(['fetch-data']);

const visible = ref(false);
const loading = ref(false);

const statusOptions = [
    { label: 'Active', value: 1 },
    { label: 'Inactive', value: 0 },
];

const form = useForm({
    id: null,
    name: '',
    description: '',
    status: 1,
});

const isEditMode = computed(() => form.id !== null);

const resetForm = (payload = {}) => {
    const defaults = {
        id: null,
        name: '',
        description: '',
        status: 1,
        ...payload,
    };

    form.defaults(defaults);
    form.reset();
    Object.assign(form, defaults);
    form.clearErrors();
};

const openNew = () => {
    resetForm();
    visible.value = true;
};

const openEdit = async (id) => {
    loading.value = true;
    form.clearErrors();

    try {
        const response = await axios.get(route('appointment-type.edit', id), {
            headers: {
                Accept: 'application/json',
            },
        });

        resetForm({
            id: response.data.id,
            name: response.data.name ?? '',
            description: response.data.description ?? '',
            status: Number(response.data.status ?? 1),
        });
        visible.value = true;
    } finally {
        loading.value = false;
    }
};

const hideDialog = () => {
    visible.value = false;
    loading.value = false;
    form.clearErrors();
};

const submit = () => {
    const action = isEditMode.value
        ? form.put(route('appointment-type.update', form.id), {
            preserveScroll: true,
            onSuccess: () => {
                hideDialog();
                emit('fetch-data');
            },
        })
        : form.post(route('appointment-type.store'), {
            preserveScroll: true,
            onSuccess: () => {
                hideDialog();
                emit('fetch-data');
            },
        });

    return action;
};

defineExpose({
    openNew,
    openEdit,
});
</script>

<template>
    <Dialog
        v-model:visible="visible"
        modal
        :header="isEditMode ? 'Edit Appointment Type' : 'Add Appointment Type'"
        :style="{ width: 'min(720px, 96vw)' }"
    >
        <div v-if="loading" class="py-10 text-center text-sm text-slate-500">Loading appointment type...</div>

        <div v-else class="grid gap-5 md:grid-cols-2">
            <div class="md:col-span-2">
                <label for="name" class="mb-2 block text-sm font-medium text-slate-700">
                    Name
                </label>
                <InputText
                    id="name"
                    v-model.trim="form.name"
                    fluid
                    autocomplete="off"
                    :invalid="!!form.errors.name"
                />
                <small v-if="form.errors.name" class="mt-1 block text-red-500">
                    {{ form.errors.name }}
                </small>
            </div>

            <div class="md:col-span-2">
                <label for="description" class="mb-2 block text-sm font-medium text-slate-700">
                    Description
                </label>
                <Textarea
                    id="description"
                    v-model="form.description"
                    rows="4"
                    fluid
                    :invalid="!!form.errors.description"
                />
                <small v-if="form.errors.description" class="mt-1 block text-red-500">
                    {{ form.errors.description }}
                </small>
            </div>

            <div>
                <label for="status" class="mb-2 block text-sm font-medium text-slate-700">
                    Status
                </label>
                <Select
                    id="status"
                    v-model="form.status"
                    :options="statusOptions"
                    optionLabel="label"
                    optionValue="value"
                    fluid
                    :invalid="!!form.errors.status"
                />
                <small v-if="form.errors.status" class="mt-1 block text-red-500">
                    {{ form.errors.status }}
                </small>
            </div>
        </div>

        <template #footer>
            <div class="flex justify-end gap-3">
                <Button label="Cancel" severity="secondary" outlined @click="hideDialog" />
                <Button
                    :label="isEditMode ? 'Update' : 'Save'"
                    icon="pi pi-save"
                    :loading="form.processing"
                    @click="submit"
                />
            </div>
        </template>
    </Dialog>
</template>

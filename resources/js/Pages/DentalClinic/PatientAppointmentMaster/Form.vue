<script setup>
import { useForm } from '@inertiajs/vue3';
import axios from 'axios';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import { ref } from 'vue';
import AppointmentForm from './Partials/AppointmentForm.vue';
import { createPatientAppointmentForm } from './formOptions';

const emit = defineEmits(['fetch-data']);

const form = useForm(createPatientAppointmentForm());
const modelDialog = ref(false);
const loading = ref(false);

const openNew = () => {
    const payload = createPatientAppointmentForm();
    form.defaults(payload);
    form.reset();
    Object.assign(form, payload);
    form.clearErrors();
    modelDialog.value = true;
};

const openEdit = async (id) => {
    form.clearErrors();
    loading.value = true;

    try {
        const response = await axios.get(route('dental-patient-appointments.edit', id), {
            headers: {
                Accept: 'application/json',
            },
        });

        const payload = createPatientAppointmentForm(response.data.patient);
        form.defaults(payload);
        form.reset();
        Object.assign(form, payload);
        modelDialog.value = true;
    } finally {
        loading.value = false;
    }
};

const hideDialog = () => {
    modelDialog.value = false;
    form.clearErrors();
};

const submit = () => {
    const isEditMode = Boolean(form.id);
    const routeUrl = isEditMode
        ? route('dental-patient-appointments.update', form.id)
        : route('dental-patient-appointments.store');
    const method = isEditMode ? 'put' : 'post';

    form[method](routeUrl, {
        preserveScroll: true,
        onSuccess: () => {
            hideDialog();
            emit('fetch-data');
        },
    });
};

defineExpose({
    openNew,
    openEdit,
});
</script>

<template>
    <Dialog
        v-model:visible="modelDialog"
        modal
        maximizable
        :header="form.id ? 'Edit Patient Appointment' : 'Add Patient Appointment'"
        :style="{ width: 'min(1280px, 96vw)' }"
    >
        <div v-if="loading" class="py-10 text-center text-sm text-slate-500">
            Loading patient data...
        </div>

        <AppointmentForm
            v-else
            :form="form"
            heading=""
            subheading=""
            :is-edit="Boolean(form.id)"
            :show-header="false"
            :show-actions="false"
        />

        <template #footer>
            <div class="flex justify-end gap-3">
                <Button label="Cancel" severity="secondary" outlined @click="hideDialog" />
                <Button
                    :label="form.id ? 'Update Patient Appointment' : 'Save Patient Appointment'"
                    icon="pi pi-save"
                    :loading="form.processing"
                    @click="submit"
                />
            </div>
        </template>
    </Dialog>
</template>

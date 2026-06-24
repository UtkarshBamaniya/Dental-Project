<script setup>
import { useForm } from '@inertiajs/vue3';
import axios from 'axios';
import Button from 'primevue/button';
import Card from 'primevue/card';
import Dialog from 'primevue/dialog';
import { computed, ref } from 'vue';
import BillingForm from './Components/BillingForm.vue';
import MedicalHistoryForm from './Components/MedicalHistoryForm.vue';
import PatientAppointmentForm from './Components/PatientAppointmentForm.vue';
import PatientDetailsForm from './Components/PatientDetailsForm.vue';
import {
    createBillingForm,
    createMedicalHistoryForm,
    createPatientAppointmentForm,
    createPatientForm,
} from './formOptions';

const emit = defineEmits(['fetch-data']);

const mode = ref('create');
const visible = ref(false);
const loading = ref(false);
const form = useForm(createPatientAppointmentForm());

const isCreateMode = computed(() => mode.value === 'create');

const resetForm = (payload) => {
    form.defaults(payload);
    form.reset();
    Object.assign(form, payload);
    form.clearErrors();
};

const openNew = () => {
    mode.value = 'create';
    resetForm(createPatientAppointmentForm());
    visible.value = true;
};

const openEdit = async (id) => {
    mode.value = 'edit-patient';
    form.clearErrors();
    loading.value = true;

    try {
        const response = await axios.get(route('dental-patient-appointments.edit', id), {
            headers: {
                Accept: 'application/json',
            },
        });

        resetForm({
            ...createPatientForm(response.data.patient),
            medical_history: createMedicalHistoryForm(response.data.medicalHistory),
            appointment: createPatientAppointmentForm().appointment,
            billing: createBillingForm(),
        });
        visible.value = true;
    } finally {
        loading.value = false;
    }
};

const hideDialog = () => {
    visible.value = false;
    form.clearErrors();
};

const submit = () => {
    const request = isCreateMode.value
        ? form.post(route('dental-patient-appointments.store'), {
            preserveScroll: true,
            onSuccess: () => {
                hideDialog();
                emit('fetch-data');
            },
        })
        : form.put(route('dental-patient-appointments.update', form.id), {
            preserveScroll: true,
            onSuccess: () => {
                hideDialog();
                emit('fetch-data');
            },
        });

    return request;
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
        maximizable
        :header="isCreateMode ? 'Add Patient Appointment' : 'Edit Patient Details'"
        :style="{ width: 'min(1200px, 96vw)' }"
    >
        <div v-if="loading" class="py-10 text-center text-sm text-slate-500">Loading patient data...</div>

        <div v-else class="space-y-6">
            <Card>
                <template #title>{{ isCreateMode ? 'Patient Details' : 'Patient Profile' }}</template>
                <template #content>
                    <PatientDetailsForm :form="form" :errors="form.errors" />
                </template>
            </Card>

            <template v-if="isCreateMode">
                <Card>
                    <template #title>Medical History</template>
                    <template #content>
                        <MedicalHistoryForm :form="form.medical_history" :errors="form.errors" />
                    </template>
                </Card>

                <Card>
                    <template #title>First Appointment</template>
                    <template #content>
                        <PatientAppointmentForm :form="form.appointment" :errors="form.errors" />
                    </template>
                </Card>

                <Card>
                    <template #title>Billing Details</template>
                    <template #content>
                        <BillingForm :form="form.billing" :errors="form.errors" />
                    </template>
                </Card>
            </template>
        </div>

        <template #footer>
            <div class="flex justify-end gap-3">
                <Button label="Cancel" severity="secondary" outlined @click="hideDialog" />
                <Button
                    :label="isCreateMode ? 'Save Patient Appointment' : 'Update Patient Details'"
                    icon="pi pi-save"
                    :loading="form.processing"
                    @click="submit"
                />
            </div>
        </template>
    </Dialog>
</template>

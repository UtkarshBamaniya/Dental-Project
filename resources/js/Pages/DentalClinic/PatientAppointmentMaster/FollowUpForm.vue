<script setup>
import { useForm } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Card from 'primevue/card';
import Dialog from 'primevue/dialog';
import { computed, ref } from 'vue';
import BillingForm from './Components/BillingForm.vue';
import PatientAppointmentForm from './Components/PatientAppointmentForm.vue';
import { createFollowUpForm } from './formOptions';

const emit = defineEmits(['fetch-data']);

const visible = ref(false);
const dialogMode = ref('follow-up');
const patientContext = ref(null);
const appointmentContext = ref(null);
const followUpForm = useForm(createFollowUpForm());

const isEditMode = computed(() => dialogMode.value === 'edit');
const headerText = computed(() => isEditMode.value ? 'Edit Appointment' : 'Add Follow-up Appointment');

const resetForm = (payload) => {
    followUpForm.defaults(payload);
    followUpForm.reset();
    Object.assign(followUpForm, payload);
    followUpForm.clearErrors();
};

const openNew = (patient) => {
    dialogMode.value = 'follow-up';
    patientContext.value = patient;
    appointmentContext.value = null;
    resetForm(createFollowUpForm());
    visible.value = true;
};

const openEdit = (patient, appointment) => {
    dialogMode.value = 'edit';
    patientContext.value = patient;
    appointmentContext.value = appointment;
    resetForm(createFollowUpForm(appointment));
    visible.value = true;
};

const hideDialog = () => {
    visible.value = false;
    patientContext.value = null;
    appointmentContext.value = null;
    followUpForm.clearErrors();
};

const submit = () => {
    if (isEditMode.value && appointmentContext.value?.id) {
        followUpForm.put(`${route('dental-patient-appointments.update', appointmentContext.value.id)}?entity=appointment`, {
            preserveScroll: true,
            onSuccess: () => {
                hideDialog();
                emit('fetch-data');
            },
        });

        return;
    }

    if (!patientContext.value?.id) {
        return;
    }

    followUpForm.post(route('dental-patient-appointments.follow-up', patientContext.value.id), {
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
        v-model:visible="visible"
        modal
        maximizable
        :header="headerText"
        :style="{ width: 'min(1080px, 96vw)' }"
    >
        <div class="space-y-6">
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-sm font-semibold text-slate-900">
                    {{ patientContext?.full_name || patientContext?.patient_name || 'Patient' }}
                </p>
                <p class="text-xs text-slate-500">
                    {{ patientContext?.patient_code || 'Patient code pending' }} | {{ patientContext?.mobile_no || 'No mobile number' }}
                </p>
            </div>

            <Card>
                <template #title>{{ isEditMode ? 'Appointment Details' : 'Follow-up Appointment Details' }}</template>
                <template #content>
                    <PatientAppointmentForm :form="followUpForm.appointment" :errors="followUpForm.errors" :disable-visit-type="!isEditMode" />
                </template>
            </Card>

            <Card>
                <template #title>Billing Details</template>
                <template #content>
                    <BillingForm :form="followUpForm.billing" :errors="followUpForm.errors" />
                </template>
            </Card>
        </div>

        <template #footer>
            <div class="flex justify-end gap-3">
                <Button label="Cancel" severity="secondary" outlined @click="hideDialog" />
                <Button :label="isEditMode ? 'Update Appointment' : 'Save Follow-up'" icon="pi pi-save" :loading="followUpForm.processing" @click="submit" />
            </div>
        </template>
    </Dialog>
</template>

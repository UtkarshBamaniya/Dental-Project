<script setup>
import { useForm } from '@inertiajs/vue3';
import Button from 'primevue/button';
import DatePicker from 'primevue/datepicker';
import Dialog from 'primevue/dialog';
import InputNumber from 'primevue/inputnumber';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import Textarea from 'primevue/textarea';
import { ref } from 'vue';
import {
    appointmentStatusOptions,
    appointmentTypeOptions,
    createFollowUpForm,
    paymentModeOptions,
    paymentStatusOptions,
    priorityOptions,
    problemAreaOptions,
} from './formOptions';

const emit = defineEmits(['fetch-data']);

const visible = ref(false);
const patientContext = ref(null);
const followUpForm = useForm(createFollowUpForm());
const optionify = (values) => values.map((value) => ({ label: value, value }));

const openNew = (patient) => {
    patientContext.value = patient;
    const payload = createFollowUpForm();
    followUpForm.defaults(payload);
    followUpForm.reset();
    Object.assign(followUpForm, payload);
    followUpForm.clearErrors();
    visible.value = true;
};

const hideDialog = () => {
    visible.value = false;
    patientContext.value = null;
    followUpForm.clearErrors();
};

const submit = () => {
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
});
</script>

<template>
    <Dialog
        v-model:visible="visible"
        modal
        maximizable
        header="Add Follow-up Appointment"
        :style="{ width: 'min(1080px, 96vw)' }"
    >
        <div class="mb-5 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
            <p class="text-sm font-semibold text-slate-900">
                {{ patientContext?.patient_name || 'Patient' }}
            </p>
            <p class="text-xs text-slate-500">
                {{ patientContext?.patient_code || 'Patient code pending' }} | {{ patientContext?.mobile_no || 'No mobile number' }}
            </p>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
                <label class="mb-2 block text-sm font-medium text-slate-700">Appointment Date</label>
                <DatePicker v-model="followUpForm.appointment.appointment_date" show-icon date-format="yy-mm-dd" class="w-full" />
                <small v-if="followUpForm.errors['appointment.appointment_date']" class="mt-1 block text-rose-500">
                    {{ followUpForm.errors['appointment.appointment_date'] }}
                </small>
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-slate-700">Appointment Time</label>
                <DatePicker v-model="followUpForm.appointment.appointment_time" time-only show-icon hour-format="12" class="w-full" />
                <small v-if="followUpForm.errors['appointment.appointment_time']" class="mt-1 block text-rose-500">
                    {{ followUpForm.errors['appointment.appointment_time'] }}
                </small>
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-slate-700">Doctor ID</label>
                <InputNumber v-model="followUpForm.appointment.doctor_id" :use-grouping="false" class="w-full" />
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-slate-700">Appointment Type</label>
                <Select
                    v-model="followUpForm.appointment.appointment_type"
                    :options="optionify(appointmentTypeOptions)"
                    option-label="label"
                    option-value="value"
                    placeholder="Select appointment type"
                    class="w-full"
                />
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-slate-700">Problem Area</label>
                <Select
                    v-model="followUpForm.appointment.problem_area"
                    :options="optionify(problemAreaOptions)"
                    option-label="label"
                    option-value="value"
                    placeholder="Select problem area"
                    class="w-full"
                />
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-slate-700">Priority</label>
                <Select
                    v-model="followUpForm.appointment.priority"
                    :options="optionify(priorityOptions)"
                    option-label="label"
                    option-value="value"
                    class="w-full"
                />
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-slate-700">Appointment Status</label>
                <Select
                    v-model="followUpForm.appointment.status"
                    :options="optionify(appointmentStatusOptions)"
                    option-label="label"
                    option-value="value"
                    class="w-full"
                />
            </div>

            <div class="md:col-span-2">
                <label class="mb-2 block text-sm font-medium text-slate-700">Chief Complaint</label>
                <Textarea v-model="followUpForm.appointment.chief_complaint" rows="3" class="w-full" />
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-slate-700">Tooth No</label>
                <InputText v-model="followUpForm.appointment.tooth_no" class="w-full" />
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-slate-700">Consultation Fee</label>
                <InputNumber v-model="followUpForm.billing.consultation_fee" mode="currency" currency="INR" locale="en-IN" class="w-full" />
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-slate-700">Treatment Estimate</label>
                <InputNumber v-model="followUpForm.billing.treatment_estimate" mode="currency" currency="INR" locale="en-IN" class="w-full" />
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-slate-700">Discount</label>
                <InputNumber v-model="followUpForm.billing.discount" mode="currency" currency="INR" locale="en-IN" class="w-full" />
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-slate-700">Paid Amount</label>
                <InputNumber v-model="followUpForm.billing.paid_amount" mode="currency" currency="INR" locale="en-IN" class="w-full" />
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-slate-700">Payment Mode</label>
                <Select
                    v-model="followUpForm.billing.payment_mode"
                    :options="optionify(paymentModeOptions)"
                    option-label="label"
                    option-value="value"
                    placeholder="Select payment mode"
                    class="w-full"
                />
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-slate-700">Payment Status</label>
                <Select
                    v-model="followUpForm.billing.payment_status"
                    :options="optionify(paymentStatusOptions)"
                    option-label="label"
                    option-value="value"
                    class="w-full"
                />
            </div>

            <div class="md:col-span-2">
                <label class="mb-2 block text-sm font-medium text-slate-700">Notes</label>
                <Textarea v-model="followUpForm.appointment.notes" rows="3" class="w-full" />
            </div>

            <div class="md:col-span-2">
                <label class="mb-2 block text-sm font-medium text-slate-700">Billing Remarks</label>
                <Textarea v-model="followUpForm.billing.remarks" rows="3" class="w-full" />
            </div>
        </div>

        <template #footer>
            <div class="flex justify-end gap-3">
                <Button label="Cancel" severity="secondary" outlined @click="hideDialog" />
                <Button label="Save Follow-up" icon="pi pi-save" :loading="followUpForm.processing" @click="submit" />
            </div>
        </template>
    </Dialog>
</template>

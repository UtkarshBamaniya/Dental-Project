<script setup>
import { computed } from 'vue';
import AppointmentType from '@/Pages/Common/DropDown/AppointmentType.vue';
import DatePicker from 'primevue/datepicker';
import InputNumber from 'primevue/inputnumber';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import Textarea from 'primevue/textarea';
import {
    appointmentStatusOptions,
    priorityOptions,
    problemAreaOptions,
    visitTypeOptions,
} from '../formOptions';

const props = defineProps({
    form: {
        type: Object,
        required: true,
    },
    errors: {
        type: Object,
        default: () => ({}),
    },
    prefix: {
        type: String,
        default: 'appointment',
    },
    disableVisitType: {
        type: Boolean,
        default: false,
    },
});

const optionify = (values) => values.map((value) => ({ label: value, value }));
const errorKey = (field) => `${props.prefix}.${field}`;
const inputClass = (field) => computed(() => ['w-full', props.errors[errorKey(field)] ? 'p-invalid' : '']);
</script>

<template>
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
        <div>
            <label class="mb-2 block text-sm font-medium text-slate-700">Appointment Date <span class="text-rose-500">*</span></label>
            <DatePicker v-model="form.appointment_date" show-icon date-format="yy-mm-dd" :class="inputClass('appointment_date').value" />
            <small v-if="errors[errorKey('appointment_date')]" class="mt-1 block text-rose-500">{{ errors[errorKey('appointment_date')] }}</small>
        </div>

        <div>
            <label class="mb-2 block text-sm font-medium text-slate-700">Appointment Time <span class="text-rose-500">*</span></label>
            <DatePicker v-model="form.appointment_time" time-only show-icon hour-format="12" :class="inputClass('appointment_time').value" />
            <small v-if="errors[errorKey('appointment_time')]" class="mt-1 block text-rose-500">{{ errors[errorKey('appointment_time')] }}</small>
        </div>

        <div>
            <label class="mb-2 block text-sm font-medium text-slate-700">Doctor</label>
            <InputNumber v-model="form.doctor_id" :use-grouping="false" :class="inputClass('doctor_id').value" />
        </div>

        <div>
            <label class="mb-2 block text-sm font-medium text-slate-700">Visit Type <span class="text-rose-500">*</span></label>
            <Select
                v-model="form.visit_type"
                :options="optionify(visitTypeOptions)"
                option-label="label"
                option-value="value"
                :disabled="disableVisitType"
                :class="inputClass('visit_type').value"
            />
            <small v-if="errors[errorKey('visit_type')]" class="mt-1 block text-rose-500">{{ errors[errorKey('visit_type')] }}</small>
        </div>

        <div>
            <label class="mb-2 block text-sm font-medium text-slate-700">Appointment Type</label>
            <AppointmentType
                v-model="form.appointment_type_id"
                placeholder="Select appointment type"
                :input-class="inputClass('appointment_type_id').value"
                :invalid="!!errors[errorKey('appointment_type_id')]"
            />
            <small v-if="errors[errorKey('appointment_type_id')]" class="mt-1 block text-rose-500">{{ errors[errorKey('appointment_type_id')] }}</small>
        </div>

        <div class="md:col-span-2 xl:col-span-3">
            <label class="mb-2 block text-sm font-medium text-slate-700">Chief Complaint</label>
            <Textarea v-model="form.chief_complaint" rows="3" :class="inputClass('chief_complaint').value" />
        </div>

        <div>
            <label class="mb-2 block text-sm font-medium text-slate-700">Problem Area</label>
            <Select
                v-model="form.problem_area"
                :options="optionify(problemAreaOptions)"
                option-label="label"
                option-value="value"
                placeholder="Select problem area"
                :class="inputClass('problem_area').value"
            />
        </div>

        <div>
            <label class="mb-2 block text-sm font-medium text-slate-700">Tooth No</label>
            <InputText v-model="form.tooth_no" :class="inputClass('tooth_no').value" />
        </div>

        <div>
            <label class="mb-2 block text-sm font-medium text-slate-700">Priority <span class="text-rose-500">*</span></label>
            <Select
                v-model="form.priority"
                :options="optionify(priorityOptions)"
                option-label="label"
                option-value="value"
                :class="inputClass('priority').value"
            />
            <small v-if="errors[errorKey('priority')]" class="mt-1 block text-rose-500">{{ errors[errorKey('priority')] }}</small>
        </div>

        <div>
            <label class="mb-2 block text-sm font-medium text-slate-700">Appointment Status <span class="text-rose-500">*</span></label>
            <Select
                v-model="form.status"
                :options="optionify(appointmentStatusOptions)"
                option-label="label"
                option-value="value"
                :class="inputClass('status').value"
            />
            <small v-if="errors[errorKey('status')]" class="mt-1 block text-rose-500">{{ errors[errorKey('status')] }}</small>
        </div>

        <div class="md:col-span-2">
            <label class="mb-2 block text-sm font-medium text-slate-700">Notes</label>
            <Textarea v-model="form.notes" rows="3" :class="inputClass('notes').value" />
        </div>
    </div>
</template>

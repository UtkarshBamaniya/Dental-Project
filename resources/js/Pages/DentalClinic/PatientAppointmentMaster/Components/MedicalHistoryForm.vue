<script setup>
import { computed } from 'vue';
import Select from 'primevue/select';
import Textarea from 'primevue/textarea';
import { bloodGroupOptions } from '../formOptions';
import MedicalDetails from '@/Pages/Common/DropDown/Medical.vue';

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
        default: 'medical_history',
    },
});

const optionify = (values) => values.map((value) => ({ label: value, value }));
const errorKey = (field) => `${props.prefix}.${field}`;
const inputClass = (field) => computed(() => ['w-full', props.errors[errorKey(field)] ? 'p-invalid' : '']);
</script>

<template>
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <div>
            <label class="mb-2 block text-sm font-medium text-slate-700">Blood Group</label>
            <Select
                v-model="form.blood_group"
                :options="optionify(bloodGroupOptions)"
                option-label="label"
                option-value="value"
                placeholder="Select blood group"
                :class="inputClass('blood_group').value"
            />
        </div>

        <div class="md:col-span-2">
            <label class="mb-2 block text-sm font-medium text-slate-700">Medical Details</label>
            <MedicalDetails v-model:medicals="form.medical_id" />
        </div>

        <div class="md:col-span-2">
            <label class="mb-2 block text-sm font-medium text-slate-700">Allergy Details</label>
            <Textarea v-model="form.allergy_details" rows="3" :class="inputClass('allergy_details').value" />
        </div>

        <div class="md:col-span-2">
            <label class="mb-2 block text-sm font-medium text-slate-700">Current Medicine</label>
            <Textarea v-model="form.current_medicine" rows="3" :class="inputClass('current_medicine').value" />
        </div>

        <div class="md:col-span-2">
            <label class="mb-2 block text-sm font-medium text-slate-700">Previous Dental Treatment</label>
            <Textarea v-model="form.previous_dental_treatment" rows="3" :class="inputClass('previous_dental_treatment').value" />
        </div>

        <div class="md:col-span-2">
            <label class="mb-2 block text-sm font-medium text-slate-700">Other Medical Notes</label>
            <Textarea v-model="form.other_medical_notes" rows="4" :class="inputClass('other_medical_notes').value" />
        </div>
    </div>
</template>

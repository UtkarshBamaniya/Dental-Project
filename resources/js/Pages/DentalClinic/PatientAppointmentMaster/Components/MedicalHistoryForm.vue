<script setup>
import { computed, watch } from 'vue';
import Checkbox from 'primevue/checkbox';
import Select from 'primevue/select';
import Textarea from 'primevue/textarea';
import { bloodGroupOptions } from '../formOptions';

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

watch(
    () => props.form.allergy,
    (value) => {
        if (!value) {
            props.form.allergy_details = '';
        }
    },
);
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
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3">
                <label class="flex items-center gap-2 rounded-xl border border-slate-200 p-3 text-sm text-slate-700">
                    <Checkbox v-model="form.diabetes" binary />
                    <span>Diabetes</span>
                </label>
                <label class="flex items-center gap-2 rounded-xl border border-slate-200 p-3 text-sm text-slate-700">
                    <Checkbox v-model="form.blood_pressure" binary />
                    <span>Blood Pressure</span>
                </label>
                <label class="flex items-center gap-2 rounded-xl border border-slate-200 p-3 text-sm text-slate-700">
                    <Checkbox v-model="form.heart_disease" binary />
                    <span>Heart Disease</span>
                </label>
                <label class="flex items-center gap-2 rounded-xl border border-slate-200 p-3 text-sm text-slate-700">
                    <Checkbox v-model="form.allergy" binary />
                    <span>Allergy</span>
                </label>
                <label class="flex items-center gap-2 rounded-xl border border-slate-200 p-3 text-sm text-slate-700">
                    <Checkbox v-model="form.pregnancy_status" binary />
                    <span>Pregnancy Status</span>
                </label>
            </div>
        </div>

        <div class="md:col-span-2">
            <label class="mb-2 block text-sm font-medium text-slate-700">Allergy Details</label>
            <Textarea
                v-model="form.allergy_details"
                rows="3"
                :disabled="!form.allergy"
                :class="inputClass('allergy_details').value"
            />
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

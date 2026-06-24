<script setup>
import { computed, watch } from 'vue';
import DatePicker from 'primevue/datepicker';
import InputNumber from 'primevue/inputnumber';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import Textarea from 'primevue/textarea';
import { genderOptions, statusOptions } from '../formOptions';

const props = defineProps({
    form: {
        type: Object,
        required: true,
    },
    errors: {
        type: Object,
        default: () => ({}),
    },
    showPatientCode: {
        type: Boolean,
        default: true,
    },
});

const optionify = (values) => values.map((value) => ({ label: value, value }));
const inputClass = (field) => computed(() => ['w-full', props.errors[field] ? 'p-invalid' : '']);

watch(
    () => props.form.date_of_birth,
    (value) => {
        if (!value) {
            props.form.age = null;
            return;
        }

        const birthDate = value instanceof Date ? value : new Date(value);
        const today = new Date();
        let age = today.getFullYear() - birthDate.getFullYear();
        const monthDiff = today.getMonth() - birthDate.getMonth();

        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
            age -= 1;
        }

        props.form.age = age >= 0 ? age : null;
    },
);
</script>

<template>
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <div v-if="showPatientCode">
            <label class="mb-2 block text-sm font-medium text-slate-700">Patient Code</label>
            <InputText v-model="form.patient_code" :class="inputClass('patient_code').value" placeholder="Auto-generated if left blank" />
            <small v-if="errors.patient_code" class="mt-1 block text-rose-500">{{ errors.patient_code }}</small>
        </div>

        <div>
            <label class="mb-2 block text-sm font-medium text-slate-700">Status <span class="text-rose-500">*</span></label>
            <Select
                v-model="form.status"
                :options="optionify(statusOptions)"
                option-label="label"
                option-value="value"
                placeholder="Select status"
                :class="inputClass('status').value"
            />
            <small v-if="errors.status" class="mt-1 block text-rose-500">{{ errors.status }}</small>
        </div>

        <div>
            <label class="mb-2 block text-sm font-medium text-slate-700">First Name <span class="text-rose-500">*</span></label>
            <InputText v-model="form.first_name" :class="inputClass('first_name').value" />
            <small v-if="errors.first_name" class="mt-1 block text-rose-500">{{ errors.first_name }}</small>
        </div>

        <div>
            <label class="mb-2 block text-sm font-medium text-slate-700">Middle Name</label>
            <InputText v-model="form.middle_name" :class="inputClass('middle_name').value" />
        </div>

        <div>
            <label class="mb-2 block text-sm font-medium text-slate-700">Last Name</label>
            <InputText v-model="form.last_name" :class="inputClass('last_name').value" />
        </div>

        <div>
            <label class="mb-2 block text-sm font-medium text-slate-700">Gender</label>
            <Select
                v-model="form.gender"
                :options="optionify(genderOptions)"
                option-label="label"
                option-value="value"
                placeholder="Select gender"
                :class="inputClass('gender').value"
            />
            <small v-if="errors.gender" class="mt-1 block text-rose-500">{{ errors.gender }}</small>
        </div>

        <div>
            <label class="mb-2 block text-sm font-medium text-slate-700">Date of Birth</label>
            <DatePicker
                v-model="form.date_of_birth"
                show-icon
                date-format="yy-mm-dd"
                :max-date="new Date()"
                :class="inputClass('date_of_birth').value"
            />
        </div>

        <div>
            <label class="mb-2 block text-sm font-medium text-slate-700">Age</label>
            <InputNumber
                v-model="form.age"
                :use-grouping="false"
                :min="0"
                :max="120"
                :class="inputClass('age').value"
            />
            <small v-if="errors.age" class="mt-1 block text-rose-500">{{ errors.age }}</small>
        </div>

        <div>
            <label class="mb-2 block text-sm font-medium text-slate-700">Mobile No <span class="text-rose-500">*</span></label>
            <InputText v-model="form.mobile_no" :class="inputClass('mobile_no').value" />
            <small v-if="errors.mobile_no" class="mt-1 block text-rose-500">{{ errors.mobile_no }}</small>
        </div>

        <div>
            <label class="mb-2 block text-sm font-medium text-slate-700">Alternate Mobile No</label>
            <InputText v-model="form.alternate_mobile_no" :class="inputClass('alternate_mobile_no').value" />
        </div>

        <div class="md:col-span-2">
            <label class="mb-2 block text-sm font-medium text-slate-700">Email</label>
            <InputText v-model="form.email" :class="inputClass('email').value" />
            <small v-if="errors.email" class="mt-1 block text-rose-500">{{ errors.email }}</small>
        </div>

        <div class="md:col-span-2">
            <label class="mb-2 block text-sm font-medium text-slate-700">Address</label>
            <Textarea v-model="form.address" rows="3" :class="inputClass('address').value" />
        </div>

        <div>
            <label class="mb-2 block text-sm font-medium text-slate-700">City</label>
            <InputText v-model="form.city" :class="inputClass('city').value" />
        </div>

        <div>
            <label class="mb-2 block text-sm font-medium text-slate-700">State</label>
            <InputText v-model="form.state" :class="inputClass('state').value" />
        </div>

        <div>
            <label class="mb-2 block text-sm font-medium text-slate-700">Pincode</label>
            <InputText v-model="form.pincode" :class="inputClass('pincode').value" />
        </div>

        <div>
            <label class="mb-2 block text-sm font-medium text-slate-700">Occupation</label>
            <InputText v-model="form.occupation" :class="inputClass('occupation').value" />
        </div>

        <div class="md:col-span-2">
            <label class="mb-2 block text-sm font-medium text-slate-700">Referred By</label>
            <InputText v-model="form.referred_by" :class="inputClass('referred_by').value" />
        </div>
    </div>
</template>

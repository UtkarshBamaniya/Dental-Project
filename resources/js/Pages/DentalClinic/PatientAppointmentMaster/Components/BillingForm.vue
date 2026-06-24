<script setup>
import { computed } from 'vue';
import InputNumber from 'primevue/inputnumber';
import Select from 'primevue/select';
import Textarea from 'primevue/textarea';
import { paymentModeOptions, paymentStatusOptions } from '../formOptions';

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
        default: 'billing',
    },
});

const optionify = (values) => values.map((value) => ({ label: value, value }));
const errorKey = (field) => `${props.prefix}.${field}`;
const inputClass = (field) => computed(() => ['w-full', props.errors[errorKey(field)] ? 'p-invalid' : '']);
</script>

<template>
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
        <div>
            <label class="mb-2 block text-sm font-medium text-slate-700">Consultation Fee</label>
            <InputNumber v-model="form.consultation_fee" mode="currency" currency="INR" locale="en-IN" :min="0" :class="inputClass('consultation_fee').value" />
        </div>

        <div>
            <label class="mb-2 block text-sm font-medium text-slate-700">Treatment Estimate</label>
            <InputNumber v-model="form.treatment_estimate" mode="currency" currency="INR" locale="en-IN" :min="0" :class="inputClass('treatment_estimate').value" />
        </div>

        <div>
            <label class="mb-2 block text-sm font-medium text-slate-700">Discount</label>
            <InputNumber v-model="form.discount" mode="currency" currency="INR" locale="en-IN" :min="0" :class="inputClass('discount').value" />
        </div>

        <div>
            <label class="mb-2 block text-sm font-medium text-slate-700">Paid Amount</label>
            <InputNumber v-model="form.paid_amount" mode="currency" currency="INR" locale="en-IN" :min="0" :class="inputClass('paid_amount').value" />
        </div>

        <div>
            <label class="mb-2 block text-sm font-medium text-slate-700">Payment Mode</label>
            <Select
                v-model="form.payment_mode"
                :options="optionify(paymentModeOptions)"
                option-label="label"
                option-value="value"
                placeholder="Select payment mode"
                :class="inputClass('payment_mode').value"
            />
        </div>

        <div>
            <label class="mb-2 block text-sm font-medium text-slate-700">Payment Status <span class="text-rose-500">*</span></label>
            <Select
                v-model="form.payment_status"
                :options="optionify(paymentStatusOptions)"
                option-label="label"
                option-value="value"
                :class="inputClass('payment_status').value"
            />
            <small v-if="errors[errorKey('payment_status')]" class="mt-1 block text-rose-500">{{ errors[errorKey('payment_status')] }}</small>
        </div>

        <div class="md:col-span-2 xl:col-span-3">
            <label class="mb-2 block text-sm font-medium text-slate-700">Remarks</label>
            <Textarea v-model="form.remarks" rows="3" :class="inputClass('remarks').value" />
        </div>
    </div>
</template>

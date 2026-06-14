<script setup>
import { computed, watch } from 'vue';
import Button from 'primevue/button';
import Card from 'primevue/card';
import Checkbox from 'primevue/checkbox';
import DatePicker from 'primevue/datepicker';
import InputNumber from 'primevue/inputnumber';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import Textarea from 'primevue/textarea';
import {
    appointmentStatusOptions,
    appointmentTypeOptions,
    bloodGroupOptions,
    genderOptions,
    paymentModeOptions,
    paymentStatusOptions,
    priorityOptions,
    problemAreaOptions,
    statusOptions,
    visitTypeOptions,
} from '../formOptions';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    form: {
        type: Object,
        required: true,
    },
    heading: {
        type: String,
        required: true,
    },
    subheading: {
        type: String,
        default: '',
    },
    submitLabel: {
        type: String,
        default: 'Save',
    },
    cancelHref: {
        type: String,
        default: '/dental-patient-appointments',
    },
    isEdit: {
        type: Boolean,
        default: false,
    },
    showHeader: {
        type: Boolean,
        default: true,
    },
    showActions: {
        type: Boolean,
        default: true,
    },
});

const emit = defineEmits(['submit']);

const optionify = (values) => values.map((value) => ({ label: value, value }));
const inputClass = (field) => computed(() => ['w-full', props.form.errors[field] ? 'p-invalid' : '']);

const calculateAge = (date) => {
    if (!date) {
        props.form.age = null;
        return;
    }

    const today = new Date();
    let age = today.getFullYear() - date.getFullYear();
    const monthDiff = today.getMonth() - date.getMonth();

    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < date.getDate())) {
        age -= 1;
    }

    props.form.age = age >= 0 ? age : null;
};

watch(
    () => props.form.date_of_birth,
    (value) => calculateAge(value),
);

watch(
    () => props.form.medical_history.allergy,
    (value) => {
        if (!value) {
            props.form.medical_history.allergy_details = '';
        }
    },
);

const submit = () => emit('submit');
</script>

<template>
    <div class="space-y-6">
        <div v-if="showHeader" class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="text-3xl font-semibold text-slate-900">{{ heading }}</h1>
                <p v-if="subheading" class="text-sm text-slate-500">{{ subheading }}</p>
            </div>

            <div v-if="showActions" class="flex gap-3">
                <Link :href="cancelHref">
                    <Button
                        label="Back"
                        severity="secondary"
                        outlined
                    />
                </Link>
                <Button
                    :label="submitLabel"
                    icon="pi pi-save"
                    :loading="form.processing"
                    @click="submit"
                />
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
            <Card>
                <template #title>Patient Details</template>
                <template #content>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">Patient Code</label>
                            <InputText v-model="form.patient_code" :class="inputClass('patient_code').value" />
                            <small v-if="form.errors.patient_code" class="mt-1 block text-rose-500">{{ form.errors.patient_code }}</small>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">Status</label>
                            <Select
                                v-model="form.status"
                                :options="optionify(statusOptions)"
                                option-label="label"
                                option-value="value"
                                placeholder="Select status"
                                :class="inputClass('status').value"
                            />
                            <small v-if="form.errors.status" class="mt-1 block text-rose-500">{{ form.errors.status }}</small>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">First Name <span class="text-rose-500">*</span></label>
                            <InputText v-model="form.first_name" :class="inputClass('first_name').value" />
                            <small v-if="form.errors.first_name" class="mt-1 block text-rose-500">{{ form.errors.first_name }}</small>
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
                            <small v-if="form.errors.gender" class="mt-1 block text-rose-500">{{ form.errors.gender }}</small>
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
                                :min="0"
                                :max="120"
                                :use-grouping="false"
                                :class="inputClass('age').value"
                            />
                            <small v-if="form.errors.age" class="mt-1 block text-rose-500">{{ form.errors.age }}</small>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">Mobile No <span class="text-rose-500">*</span></label>
                            <InputText v-model="form.mobile_no" :class="inputClass('mobile_no').value" />
                            <small v-if="form.errors.mobile_no" class="mt-1 block text-rose-500">{{ form.errors.mobile_no }}</small>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">Alternate Mobile No</label>
                            <InputText v-model="form.alternate_mobile_no" :class="inputClass('alternate_mobile_no').value" />
                        </div>

                        <div class="md:col-span-2">
                            <label class="mb-2 block text-sm font-medium text-slate-700">Email</label>
                            <InputText v-model="form.email" :class="inputClass('email').value" />
                            <small v-if="form.errors.email" class="mt-1 block text-rose-500">{{ form.errors.email }}</small>
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

                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">Referred By</label>
                            <InputText v-model="form.referred_by" :class="inputClass('referred_by').value" />
                        </div>
                    </div>
                </template>
            </Card>

            <Card>
                <template #title>Medical History</template>
                <template #content>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">Blood Group</label>
                            <Select
                                v-model="form.medical_history.blood_group"
                                :options="optionify(bloodGroupOptions)"
                                option-label="label"
                                option-value="value"
                                placeholder="Select blood group"
                                :class="inputClass('medical_history.blood_group').value"
                            />
                        </div>

                        <div class="md:col-span-2">
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3">
                                <label class="flex items-center gap-2 rounded-lg border border-slate-200 p-3 text-sm text-slate-700">
                                    <Checkbox v-model="form.medical_history.diabetes" binary />
                                    <span>Diabetes</span>
                                </label>
                                <label class="flex items-center gap-2 rounded-lg border border-slate-200 p-3 text-sm text-slate-700">
                                    <Checkbox v-model="form.medical_history.blood_pressure" binary />
                                    <span>Blood Pressure</span>
                                </label>
                                <label class="flex items-center gap-2 rounded-lg border border-slate-200 p-3 text-sm text-slate-700">
                                    <Checkbox v-model="form.medical_history.heart_disease" binary />
                                    <span>Heart Disease</span>
                                </label>
                                <label class="flex items-center gap-2 rounded-lg border border-slate-200 p-3 text-sm text-slate-700">
                                    <Checkbox v-model="form.medical_history.allergy" binary />
                                    <span>Allergy</span>
                                </label>
                                <label class="flex items-center gap-2 rounded-lg border border-slate-200 p-3 text-sm text-slate-700">
                                    <Checkbox v-model="form.medical_history.pregnancy_status" binary />
                                    <span>Pregnancy Status</span>
                                </label>
                            </div>
                        </div>

                        <div class="md:col-span-2">
                            <label class="mb-2 block text-sm font-medium text-slate-700">Allergy Details</label>
                            <Textarea
                                v-model="form.medical_history.allergy_details"
                                rows="3"
                                :disabled="!form.medical_history.allergy"
                                :class="inputClass('medical_history.allergy_details').value"
                            />
                        </div>

                        <div class="md:col-span-2">
                            <label class="mb-2 block text-sm font-medium text-slate-700">Current Medicine</label>
                            <Textarea v-model="form.medical_history.current_medicine" rows="3" :class="inputClass('medical_history.current_medicine').value" />
                        </div>

                        <div class="md:col-span-2">
                            <label class="mb-2 block text-sm font-medium text-slate-700">Previous Dental Treatment</label>
                            <Textarea v-model="form.medical_history.previous_dental_treatment" rows="3" :class="inputClass('medical_history.previous_dental_treatment').value" />
                        </div>

                        <div class="md:col-span-2">
                            <label class="mb-2 block text-sm font-medium text-slate-700">Other Medical Notes</label>
                            <Textarea v-model="form.medical_history.other_medical_notes" rows="4" :class="inputClass('medical_history.other_medical_notes').value" />
                        </div>
                    </div>
                </template>
            </Card>

            <Card class="xl:col-span-2">
                <template #title>Appointment Details</template>
                <template #content>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">Appointment Date <span class="text-rose-500">*</span></label>
                            <DatePicker
                                v-model="form.appointment.appointment_date"
                                show-icon
                                date-format="yy-mm-dd"
                                :class="inputClass('appointment.appointment_date').value"
                            />
                            <small v-if="form.errors['appointment.appointment_date']" class="mt-1 block text-rose-500">
                                {{ form.errors['appointment.appointment_date'] }}
                            </small>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">Appointment Time <span class="text-rose-500">*</span></label>
                            <DatePicker
                                v-model="form.appointment.appointment_time"
                                time-only
                                show-icon
                                hour-format="12"
                                :class="inputClass('appointment.appointment_time').value"
                            />
                            <small v-if="form.errors['appointment.appointment_time']" class="mt-1 block text-rose-500">
                                {{ form.errors['appointment.appointment_time'] }}
                            </small>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">Doctor ID</label>
                            <InputNumber v-model="form.appointment.doctor_id" :use-grouping="false" :class="inputClass('appointment.doctor_id').value" />
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">Visit Type</label>
                            <Select
                                v-model="form.appointment.visit_type"
                                :options="optionify(visitTypeOptions)"
                                option-label="label"
                                option-value="value"
                                placeholder="Select visit type"
                                :disabled="isEdit"
                                :class="inputClass('appointment.visit_type').value"
                            />
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">Appointment Type</label>
                            <Select
                                v-model="form.appointment.appointment_type"
                                :options="optionify(appointmentTypeOptions)"
                                option-label="label"
                                option-value="value"
                                placeholder="Select appointment type"
                                :class="inputClass('appointment.appointment_type').value"
                            />
                        </div>

                        <div class="md:col-span-2 xl:col-span-3">
                            <label class="mb-2 block text-sm font-medium text-slate-700">Chief Complaint</label>
                            <Textarea v-model="form.appointment.chief_complaint" rows="3" :class="inputClass('appointment.chief_complaint').value" />
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">Problem Area</label>
                            <Select
                                v-model="form.appointment.problem_area"
                                :options="optionify(problemAreaOptions)"
                                option-label="label"
                                option-value="value"
                                placeholder="Select problem area"
                                :class="inputClass('appointment.problem_area').value"
                            />
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">Tooth No</label>
                            <InputText v-model="form.appointment.tooth_no" :class="inputClass('appointment.tooth_no').value" />
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">Priority</label>
                            <Select
                                v-model="form.appointment.priority"
                                :options="optionify(priorityOptions)"
                                option-label="label"
                                option-value="value"
                                placeholder="Select priority"
                                :class="inputClass('appointment.priority').value"
                            />
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">Appointment Status</label>
                            <Select
                                v-model="form.appointment.status"
                                :options="optionify(appointmentStatusOptions)"
                                option-label="label"
                                option-value="value"
                                placeholder="Select status"
                                :class="inputClass('appointment.status').value"
                            />
                        </div>

                        <div class="md:col-span-2 xl:col-span-2">
                            <label class="mb-2 block text-sm font-medium text-slate-700">Notes</label>
                            <Textarea v-model="form.appointment.notes" rows="3" :class="inputClass('appointment.notes').value" />
                        </div>
                    </div>
                </template>
            </Card>

            <Card class="xl:col-span-2">
                <template #title>Billing Details</template>
                <template #content>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">Consultation Fee</label>
                            <InputNumber v-model="form.billing.consultation_fee" mode="currency" currency="INR" locale="en-IN" :min="0" :class="inputClass('billing.consultation_fee').value" />
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">Treatment Estimate</label>
                            <InputNumber v-model="form.billing.treatment_estimate" mode="currency" currency="INR" locale="en-IN" :min="0" :class="inputClass('billing.treatment_estimate').value" />
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">Discount</label>
                            <InputNumber v-model="form.billing.discount" mode="currency" currency="INR" locale="en-IN" :min="0" :class="inputClass('billing.discount').value" />
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">Paid Amount</label>
                            <InputNumber v-model="form.billing.paid_amount" mode="currency" currency="INR" locale="en-IN" :min="0" :class="inputClass('billing.paid_amount').value" />
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">Payment Mode</label>
                            <Select
                                v-model="form.billing.payment_mode"
                                :options="optionify(paymentModeOptions)"
                                option-label="label"
                                option-value="value"
                                placeholder="Select payment mode"
                                :class="inputClass('billing.payment_mode').value"
                            />
                            <small v-if="form.errors['billing.payment_mode']" class="mt-1 block text-rose-500">{{ form.errors['billing.payment_mode'] }}</small>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">Payment Status</label>
                            <Select
                                v-model="form.billing.payment_status"
                                :options="optionify(paymentStatusOptions)"
                                option-label="label"
                                option-value="value"
                                placeholder="Select payment status"
                                :class="inputClass('billing.payment_status').value"
                            />
                            <small v-if="form.errors['billing.payment_status']" class="mt-1 block text-rose-500">{{ form.errors['billing.payment_status'] }}</small>
                        </div>

                        <div class="md:col-span-2 xl:col-span-3">
                            <label class="mb-2 block text-sm font-medium text-slate-700">Remarks</label>
                            <Textarea v-model="form.billing.remarks" rows="3" :class="inputClass('billing.remarks').value" />
                        </div>
                    </div>
                </template>
            </Card>
        </div>
    </div>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Card from 'primevue/card';
import Dialog from 'primevue/dialog';
import Tag from 'primevue/tag';
import { computed, ref } from 'vue';
import Toolbar from '@/Pages/Common/AugComponent/Toolbar.vue';
import AppointmentDetailDialog from './Components/AppointmentDetailDialog.vue';
import AppointmentHistoryTable from './Components/AppointmentHistoryTable.vue';
import MedicalHistoryForm from './Components/MedicalHistoryForm.vue';
import PatientSidebarList from './Components/PatientSidebarList.vue';
import FollowUpForm from './FollowUpForm.vue';
import Form from './Form.vue';
import { createMedicalHistoryForm } from './formOptions';

const props = defineProps({
    patient: {
        type: Object,
        required: true,
    },
    medicalHistory: {
        type: Object,
        required: true,
    },
    summary: {
        type: Object,
        required: true,
    },
    appointments: {
        type: Array,
        default: () => [],
    },
    sidebar: {
        type: Object,
        required: true,
    },
    permissions: {
        type: Object,
        required: true,
    },
    title: {
        type: String,
        default: 'Patient Profile',
    },
    desc: {
        type: String,
        default: '',
    },
});

const flash = usePage().props.flash;
const formRef = ref(null);
const followUpRef = ref(null);
const detailVisible = ref(false);
const selectedAppointment = ref(null);
const medicalHistoryVisible = ref(false);
const appointmentDeleteVisible = ref(false);
const appointmentDeleteLoading = ref(false);
const appointmentToDelete = ref(null);
const medicalHistoryForm = useForm({
    medical_history: createMedicalHistoryForm(props.medicalHistory),
});

const fullAddress = computed(() =>
    [
        props.patient.address,
        props.patient.city,
        props.patient.state,
        props.patient.pincode,
    ].filter(Boolean).join(', '),
);

const formatCurrency = (value) => new Intl.NumberFormat('en-IN', {
    style: 'currency',
    currency: 'INR',
    minimumFractionDigits: 2,
}).format(Number(value ?? 0));

const yesNo = (value) => value ? 'Yes' : 'No';

const openMedicalHistory = () => {
    medicalHistoryForm.defaults({
        medical_history: createMedicalHistoryForm(props.medicalHistory),
    });
    medicalHistoryForm.reset();
    medicalHistoryForm.medical_history = createMedicalHistoryForm(props.medicalHistory);
    medicalHistoryForm.clearErrors();
    medicalHistoryVisible.value = true;
};

const submitMedicalHistory = () => {
    medicalHistoryForm.put(`${route('dental-patient-appointments.update', props.patient.id)}?entity=medical-history`, {
        preserveScroll: true,
        onSuccess: () => {
            medicalHistoryVisible.value = false;
        },
    });
};

const openAppointmentDetail = (appointment) => {
    selectedAppointment.value = appointment;
    detailVisible.value = true;
};

const confirmDeleteAppointment = (appointment) => {
    appointmentToDelete.value = appointment;
    appointmentDeleteVisible.value = true;
};

const deleteAppointment = () => {
    if (!appointmentToDelete.value?.id) {
        return;
    }

    appointmentDeleteLoading.value = true;

    router.delete(`${route('dental-patient-appointments.destroy', appointmentToDelete.value.id)}?entity=appointment`, {
        preserveScroll: true,
        onFinish: () => {
            appointmentDeleteLoading.value = false;
        },
        onSuccess: () => {
            appointmentDeleteVisible.value = false;
            appointmentToDelete.value = null;
        },
    });
};

const handlePrint = (appointment) => {
    if (!appointment) {
        return;
    }

    const billing = appointment.billing || {};
    const printWindow = window.open('', '_blank', 'width=900,height=700');

    if (!printWindow) {
        return;
    }

    printWindow.document.write(`
        <html>
            <head>
                <title>${appointment.appointment_no}</title>
                <style>
                    body { font-family: Arial, sans-serif; padding: 24px; color: #0f172a; }
                    h1, h2 { margin-bottom: 8px; }
                    table { width: 100%; border-collapse: collapse; margin-top: 16px; }
                    td, th { border: 1px solid #cbd5e1; padding: 10px; text-align: left; }
                    .section { margin-top: 24px; }
                </style>
            </head>
            <body>
                <h1>${props.patient.full_name}</h1>
                <p>${props.patient.patient_code} | ${props.patient.mobile_no}</p>
                <div class="section">
                    <h2>Appointment Details</h2>
                    <table>
                        <tr><th>Appointment No</th><td>${appointment.appointment_no ?? '-'}</td></tr>
                        <tr><th>Date</th><td>${appointment.appointment_date ?? '-'}</td></tr>
                        <tr><th>Time</th><td>${appointment.appointment_time ?? '-'}</td></tr>
                        <tr><th>Doctor</th><td>${appointment.doctor_name ?? 'Unassigned'}</td></tr>
                        <tr><th>Visit Type</th><td>${appointment.visit_type ?? '-'}</td></tr>
                        <tr><th>Appointment Type</th><td>${appointment.appointment_type ?? '-'}</td></tr>
                        <tr><th>Chief Complaint</th><td>${appointment.chief_complaint ?? '-'}</td></tr>
                        <tr><th>Priority</th><td>${appointment.priority ?? '-'}</td></tr>
                        <tr><th>Status</th><td>${appointment.status ?? '-'}</td></tr>
                        <tr><th>Notes</th><td>${appointment.notes ?? '-'}</td></tr>
                    </table>
                </div>
                <div class="section">
                    <h2>Billing Details</h2>
                    <table>
                        <tr><th>Consultation Fee</th><td>${formatCurrency(billing.consultation_fee)}</td></tr>
                        <tr><th>Treatment Estimate</th><td>${formatCurrency(billing.treatment_estimate)}</td></tr>
                        <tr><th>Discount</th><td>${formatCurrency(billing.discount)}</td></tr>
                        <tr><th>Paid Amount</th><td>${formatCurrency(billing.paid_amount)}</td></tr>
                        <tr><th>Payment Mode</th><td>${billing.payment_mode ?? '-'}</td></tr>
                        <tr><th>Payment Status</th><td>${billing.payment_status ?? '-'}</td></tr>
                        <tr><th>Remarks</th><td>${billing.remarks ?? '-'}</td></tr>
                    </table>
                </div>
            </body>
        </html>
    `);
    printWindow.document.close();
    printWindow.focus();
    printWindow.print();
};
</script>

<template>
    <Head :title="`${patient.full_name} - Patient Profile`" />

    <AuthenticatedLayout>
        <div class="mb-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <Toolbar :title="title" :desc="desc" icon="pi pi-user">
                <template #actions>
                    <Link :href="route('dental-patient-appointments.index')">
                        <Button label="Back to List" severity="secondary" outlined />
                    </Link>
                    <Button label="Edit Patient" icon="pi pi-pencil" outlined @click="formRef?.openEdit(patient.id)" />
                    <Button label="Add Follow-up" icon="pi pi-calendar-plus" @click="followUpRef?.openNew(patient)" />
                </template>
            </Toolbar>
        </div>

        <div v-if="flash.success" class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            {{ flash.success }}
        </div>

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_340px]">
            <div class="space-y-6">
                <div class="grid gap-6 lg:grid-cols-2">
                    <Card class="rounded-2xl shadow-sm">
                        <template #title>
                            <div class="flex items-center justify-between gap-3">
                                <span>Patient Summary</span>
                                <Tag :value="patient.status" :severity="patient.status === 'Active' ? 'success' : 'secondary'" />
                            </div>
                        </template>
                        <template #content>
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <p class="text-xs uppercase tracking-wide text-slate-400">Patient Code</p>
                                    <p class="mt-1 text-sm font-medium text-slate-800">{{ patient.patient_code }}</p>
                                </div>
                                <div>
                                    <p class="text-xs uppercase tracking-wide text-slate-400">Full Name</p>
                                    <p class="mt-1 text-sm font-medium text-slate-800">{{ patient.full_name }}</p>
                                </div>
                                <div>
                                    <p class="text-xs uppercase tracking-wide text-slate-400">Gender</p>
                                    <p class="mt-1 text-sm text-slate-700">{{ patient.gender || '-' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs uppercase tracking-wide text-slate-400">Age</p>
                                    <p class="mt-1 text-sm text-slate-700">{{ patient.age ?? '-' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs uppercase tracking-wide text-slate-400">Mobile No</p>
                                    <p class="mt-1 text-sm text-slate-700">{{ patient.mobile_no }}</p>
                                </div>
                                <div>
                                    <p class="text-xs uppercase tracking-wide text-slate-400">Email</p>
                                    <p class="mt-1 text-sm text-slate-700">{{ patient.email || '-' }}</p>
                                </div>
                                <div class="sm:col-span-2">
                                    <p class="text-xs uppercase tracking-wide text-slate-400">Address</p>
                                    <p class="mt-1 text-sm text-slate-700">{{ fullAddress || '-' }}</p>
                                </div>
                            </div>
                        </template>
                    </Card>

                    <Card class="rounded-2xl shadow-sm">
                        <template #title>
                            <div class="flex items-center justify-between gap-3">
                                <span>Medical History</span>
                                <Button label="Edit Medical History" icon="pi pi-file-edit" text @click="openMedicalHistory" />
                            </div>
                        </template>
                        <template #content>
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <p class="text-xs uppercase tracking-wide text-slate-400">Blood Group</p>
                                    <p class="mt-1 text-sm text-slate-700">{{ medicalHistory.blood_group || '-' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs uppercase tracking-wide text-slate-400">Diabetes</p>
                                    <p class="mt-1 text-sm text-slate-700">{{ yesNo(medicalHistory.diabetes) }}</p>
                                </div>
                                <div>
                                    <p class="text-xs uppercase tracking-wide text-slate-400">Blood Pressure</p>
                                    <p class="mt-1 text-sm text-slate-700">{{ yesNo(medicalHistory.blood_pressure) }}</p>
                                </div>
                                <div>
                                    <p class="text-xs uppercase tracking-wide text-slate-400">Heart Disease</p>
                                    <p class="mt-1 text-sm text-slate-700">{{ yesNo(medicalHistory.heart_disease) }}</p>
                                </div>
                                <div>
                                    <p class="text-xs uppercase tracking-wide text-slate-400">Allergy</p>
                                    <p class="mt-1 text-sm text-slate-700">{{ yesNo(medicalHistory.allergy) }}</p>
                                </div>
                                <div>
                                    <p class="text-xs uppercase tracking-wide text-slate-400">Pregnancy Status</p>
                                    <p class="mt-1 text-sm text-slate-700">{{ yesNo(medicalHistory.pregnancy_status) }}</p>
                                </div>
                                <div class="sm:col-span-2">
                                    <p class="text-xs uppercase tracking-wide text-slate-400">Allergy Details</p>
                                    <p class="mt-1 text-sm text-slate-700">{{ medicalHistory.allergy_details || '-' }}</p>
                                </div>
                                <div class="sm:col-span-2">
                                    <p class="text-xs uppercase tracking-wide text-slate-400">Current Medicine</p>
                                    <p class="mt-1 whitespace-pre-line text-sm text-slate-700">{{ medicalHistory.current_medicine || '-' }}</p>
                                </div>
                                <div class="sm:col-span-2">
                                    <p class="text-xs uppercase tracking-wide text-slate-400">Previous Dental Treatment</p>
                                    <p class="mt-1 whitespace-pre-line text-sm text-slate-700">{{ medicalHistory.previous_dental_treatment || '-' }}</p>
                                </div>
                                <div class="sm:col-span-2">
                                    <p class="text-xs uppercase tracking-wide text-slate-400">Other Medical Notes</p>
                                    <p class="mt-1 whitespace-pre-line text-sm text-slate-700">{{ medicalHistory.other_medical_notes || '-' }}</p>
                                </div>
                            </div>
                        </template>
                    </Card>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
                    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                        <p class="text-xs uppercase tracking-wide text-slate-400">Total Appointments</p>
                        <p class="mt-2 text-3xl font-semibold text-slate-900">{{ summary.total_appointments }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                        <p class="text-xs uppercase tracking-wide text-slate-400">Completed Appointments</p>
                        <p class="mt-2 text-3xl font-semibold text-emerald-600">{{ summary.completed_appointments }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                        <p class="text-xs uppercase tracking-wide text-slate-400">Pending/Scheduled</p>
                        <p class="mt-2 text-3xl font-semibold text-amber-600">{{ summary.pending_scheduled_appointments }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                        <p class="text-xs uppercase tracking-wide text-slate-400">Total Paid Amount</p>
                        <p class="mt-2 text-2xl font-semibold text-slate-900">{{ formatCurrency(summary.total_paid_amount) }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                        <p class="text-xs uppercase tracking-wide text-slate-400">Outstanding Amount</p>
                        <p class="mt-2 text-2xl font-semibold text-rose-600">{{ formatCurrency(summary.outstanding_amount) }}</p>
                    </div>
                </div>

                <Card class="rounded-2xl shadow-sm">
                    <template #title>
                        <div class="flex items-center justify-between gap-3">
                            <span>Appointment History</span>
                            <Button label="Add Follow-up" icon="pi pi-calendar-plus" @click="followUpRef?.openNew(patient)" />
                        </div>
                    </template>
                    <template #content>
                        <AppointmentHistoryTable
                            :appointments="appointments"
                            @view="openAppointmentDetail"
                            @edit="(appointment) => followUpRef?.openEdit(patient, appointment)"
                            @delete="confirmDeleteAppointment"
                            @print="handlePrint"
                        />
                    </template>
                </Card>
            </div>

            <PatientSidebarList :sidebar="sidebar" />
        </div>

        <AppointmentDetailDialog
            v-model:visible="detailVisible"
            :appointment="selectedAppointment"
            @print="handlePrint"
        />

        <Dialog v-model:visible="medicalHistoryVisible" modal maximizable header="Edit Medical History" :style="{ width: 'min(980px, 96vw)' }">
            <MedicalHistoryForm :form="medicalHistoryForm.medical_history" :errors="medicalHistoryForm.errors" />
            <template #footer>
                <div class="flex justify-end gap-3">
                    <Button label="Cancel" severity="secondary" outlined @click="medicalHistoryVisible = false" />
                    <Button label="Update Medical History" icon="pi pi-save" :loading="medicalHistoryForm.processing" @click="submitMedicalHistory" />
                </div>
            </template>
        </Dialog>

        <Dialog v-model:visible="appointmentDeleteVisible" modal header="Delete Appointment" :style="{ width: 'min(420px, 92vw)' }">
            <p class="text-sm leading-6 text-slate-600">
                Delete appointment {{ appointmentToDelete?.appointment_no || '' }}? This action cannot be undone.
            </p>
            <template #footer>
                <div class="flex justify-end gap-3">
                    <Button label="Cancel" severity="secondary" outlined @click="appointmentDeleteVisible = false" />
                    <Button label="Delete" severity="danger" :loading="appointmentDeleteLoading" @click="deleteAppointment" />
                </div>
            </template>
        </Dialog>

        <FollowUpForm ref="followUpRef" />
        <Form ref="formRef" />
    </AuthenticatedLayout>
</template>

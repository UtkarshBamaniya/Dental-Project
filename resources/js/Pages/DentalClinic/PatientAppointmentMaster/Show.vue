<script setup>
import axios from 'axios';
import Button from 'primevue/button';
import Card from 'primevue/card';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import Dialog from 'primevue/dialog';
import { computed, ref } from 'vue';

const modelDialog = ref(false);
const loading = ref(false);
const showData = ref({
    patient: {},
    medicalHistory: {},
    appointments: [],
});

const fullAddress = computed(() =>
    [
        showData.value.patient?.address,
        showData.value.patient?.city,
        showData.value.patient?.state,
        showData.value.patient?.pincode,
    ]
        .filter(Boolean)
        .join(', '),
);

const formatCurrency = (value) => new Intl.NumberFormat('en-IN', {
    style: 'currency',
    currency: 'INR',
    minimumFractionDigits: 2,
}).format(Number(value ?? 0));

const formatYesNo = (value) => (value ? 'Yes' : 'No');

const openShow = async (id) => {
    loading.value = true;

    try {
        const response = await axios.get(route('dental-patient-appointments.show', id), {
            headers: {
                Accept: 'application/json',
            },
        });

        showData.value = response.data;
        modelDialog.value = true;
    } finally {
        loading.value = false;
    }
};

const hideDialog = () => {
    modelDialog.value = false;
};

defineExpose({
    openShow,
});
</script>

<template>
    <Dialog
        v-model:visible="modelDialog"
        modal
        maximizable
        header="Patient Appointment Details"
        :style="{ width: 'min(1280px, 96vw)' }"
    >
        <div v-if="loading" class="py-10 text-center text-sm text-slate-500">
            Loading patient data...
        </div>

        <div v-else class="space-y-6">
            <div>
                <p class="text-sm font-medium uppercase tracking-wide text-sky-600">Patient Profile</p>
                <h2 class="text-3xl font-semibold text-slate-900">{{ showData.patient.full_name }}</h2>
                <p class="mt-1 text-sm text-slate-500">
                    {{ showData.patient.patient_code || 'Auto-generated patient code' }} | {{ showData.patient.mobile_no }}
                </p>
            </div>

            <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
                <Card class="xl:col-span-2">
                    <template #title>Basic Details</template>
                    <template #content>
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <p class="text-xs uppercase tracking-wide text-slate-400">Gender</p>
                                <p class="mt-1 text-sm text-slate-700">{{ showData.patient.gender || 'Not specified' }}</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-wide text-slate-400">Age</p>
                                <p class="mt-1 text-sm text-slate-700">{{ showData.patient.age ?? 'Not specified' }}</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-wide text-slate-400">Date of Birth</p>
                                <p class="mt-1 text-sm text-slate-700">{{ showData.patient.date_of_birth || 'Not specified' }}</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-wide text-slate-400">Email</p>
                                <p class="mt-1 text-sm text-slate-700">{{ showData.patient.email || 'Not specified' }}</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-wide text-slate-400">Alternate Mobile</p>
                                <p class="mt-1 text-sm text-slate-700">{{ showData.patient.alternate_mobile_no || 'Not specified' }}</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-wide text-slate-400">Occupation</p>
                                <p class="mt-1 text-sm text-slate-700">{{ showData.patient.occupation || 'Not specified' }}</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-wide text-slate-400">Referred By</p>
                                <p class="mt-1 text-sm text-slate-700">{{ showData.patient.referred_by || 'Not specified' }}</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-wide text-slate-400">Status</p>
                                <p class="mt-1 text-sm text-slate-700">{{ showData.patient.status }}</p>
                            </div>
                            <div class="md:col-span-2">
                                <p class="text-xs uppercase tracking-wide text-slate-400">Address</p>
                                <p class="mt-1 text-sm text-slate-700">{{ fullAddress || 'Not specified' }}</p>
                            </div>
                        </div>
                    </template>
                </Card>

                <Card>
                    <template #title>Medical History</template>
                    <template #content>
                        <div class="space-y-4 text-sm text-slate-700">
                            <div class="flex items-center justify-between gap-3">
                                <span>Blood Group</span>
                                <span class="font-medium">{{ showData.medicalHistory.blood_group || 'Not specified' }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <span>Diabetes</span>
                                <span class="font-medium">{{ formatYesNo(showData.medicalHistory.diabetes) }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <span>Blood Pressure</span>
                                <span class="font-medium">{{ formatYesNo(showData.medicalHistory.blood_pressure) }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <span>Heart Disease</span>
                                <span class="font-medium">{{ formatYesNo(showData.medicalHistory.heart_disease) }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <span>Allergy</span>
                                <span class="font-medium">{{ formatYesNo(showData.medicalHistory.allergy) }}</span>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-wide text-slate-400">Allergy Details</p>
                                <p class="mt-1">{{ showData.medicalHistory.allergy_details || 'None recorded' }}</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-wide text-slate-400">Current Medicine</p>
                                <p class="mt-1 whitespace-pre-line">{{ showData.medicalHistory.current_medicine || 'None recorded' }}</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-wide text-slate-400">Previous Dental Treatment</p>
                                <p class="mt-1 whitespace-pre-line">{{ showData.medicalHistory.previous_dental_treatment || 'None recorded' }}</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-wide text-slate-400">Other Notes</p>
                                <p class="mt-1 whitespace-pre-line">{{ showData.medicalHistory.other_medical_notes || 'None recorded' }}</p>
                            </div>
                        </div>
                    </template>
                </Card>
            </div>

            <Card>
                <template #title>Appointment History</template>
                <template #content>
                    <DataTable :value="showData.appointments" responsive-layout="scroll" striped-rows>
                        <Column field="appointment_no" header="Appointment No" />
                        <Column field="appointment_date" header="Date" />
                        <Column field="appointment_time" header="Time" />
                        <Column field="visit_type" header="Visit Type" />
                        <Column field="appointment_type" header="Appointment Type" />
                        <Column field="priority" header="Priority" />
                        <Column field="status" header="Status" />
                        <Column header="Billing">
                            <template #body="{ data }">
                                <div class="space-y-1 text-sm text-slate-600">
                                    <div>Fee: {{ formatCurrency(data.billing.consultation_fee) }}</div>
                                    <div>Paid: {{ formatCurrency(data.billing.paid_amount) }}</div>
                                    <div>Status: {{ data.billing.payment_status || 'Pending' }}</div>
                                </div>
                            </template>
                        </Column>
                        <Column header="Clinical Notes">
                            <template #body="{ data }">
                                <div class="space-y-1 text-sm text-slate-600">
                                    <div><span class="font-medium text-slate-700">Complaint:</span> {{ data.chief_complaint || 'N/A' }}</div>
                                    <div><span class="font-medium text-slate-700">Problem Area:</span> {{ data.problem_area || 'N/A' }}</div>
                                    <div><span class="font-medium text-slate-700">Tooth No:</span> {{ data.tooth_no || 'N/A' }}</div>
                                </div>
                            </template>
                        </Column>
                        <template #empty>
                            <div class="py-6 text-center text-sm text-slate-500">No appointments recorded yet.</div>
                        </template>
                    </DataTable>
                </template>
            </Card>
        </div>

        <template #footer>
            <Button label="Close" severity="secondary" outlined @click="hideDialog" />
        </template>
    </Dialog>
</template>

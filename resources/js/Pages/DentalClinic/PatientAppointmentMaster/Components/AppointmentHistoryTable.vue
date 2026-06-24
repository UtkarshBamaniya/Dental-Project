<script setup>
import Button from 'primevue/button';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import Tag from 'primevue/tag';

defineProps({
    appointments: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['view', 'edit', 'delete', 'print']);

const formatCurrency = (value) => new Intl.NumberFormat('en-IN', {
    style: 'currency',
    currency: 'INR',
    minimumFractionDigits: 2,
}).format(Number(value ?? 0));

const statusSeverity = (status) => ({
    Completed: 'success',
    Scheduled: 'info',
    Rescheduled: 'warn',
    Cancelled: 'danger',
    'No Show': 'contrast',
}[status] || 'secondary');

const paymentSeverity = (status) => ({
    Paid: 'success',
    Partial: 'warn',
    Pending: 'danger',
}[status] || 'secondary');
</script>

<template>
    <DataTable :value="appointments" striped-rows responsive-layout="scroll">
        <Column field="appointment_no" header="Appointment No" />
        <Column field="appointment_date" header="Date" />
        <Column field="appointment_time" header="Time" />
        <Column field="visit_type" header="Visit Type" />
        <Column field="appointment_type" header="Appointment Type" />
        <Column field="doctor_name" header="Doctor" />
        <Column field="chief_complaint" header="Chief Complaint">
            <template #body="{ data }">
                <span>{{ data.chief_complaint || '-' }}</span>
            </template>
        </Column>
        <Column field="priority" header="Priority" />
        <Column field="status" header="Status">
            <template #body="{ data }">
                <Tag :value="data.status" :severity="statusSeverity(data.status)" />
            </template>
        </Column>
        <Column header="Paid Amount">
            <template #body="{ data }">
                {{ formatCurrency(data.billing?.paid_amount) }}
            </template>
        </Column>
        <Column header="Payment Status">
            <template #body="{ data }">
                <Tag :value="data.billing?.payment_status || 'Pending'" :severity="paymentSeverity(data.billing?.payment_status)" />
            </template>
        </Column>
        <Column header="Actions" :exportable="false" style="min-width: 14rem">
            <template #body="{ data }">
                <div class="flex flex-wrap gap-2">
                    <Button icon="pi pi-eye" text rounded @click="emit('view', data)" />
                    <Button icon="pi pi-pencil" text rounded @click="emit('edit', data)" />
                    <Button icon="pi pi-trash" text rounded severity="danger" @click="emit('delete', data)" />
                    <Button icon="pi pi-print" text rounded @click="emit('print', data)" />
                </div>
            </template>
        </Column>
        <template #empty>
            <div class="py-8 text-center text-sm text-slate-500">No appointments recorded yet.</div>
        </template>
    </DataTable>
</template>

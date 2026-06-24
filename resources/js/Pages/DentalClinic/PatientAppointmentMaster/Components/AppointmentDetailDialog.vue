<script setup>
import { computed } from 'vue';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import Tag from 'primevue/tag';

const props = defineProps({
    visible: {
        type: Boolean,
        default: false,
    },
    appointment: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(['update:visible', 'print']);

const model = computed({
    get: () => props.visible,
    set: (value) => emit('update:visible', value),
});

const formatCurrency = (value) => new Intl.NumberFormat('en-IN', {
    style: 'currency',
    currency: 'INR',
    minimumFractionDigits: 2,
}).format(Number(value ?? 0));

const detailRows = computed(() => {
    const appointment = props.appointment ?? {};

    return [
        ['Appointment No', appointment.appointment_no ?? '-'],
        ['Date', appointment.appointment_date ?? '-'],
        ['Time', appointment.appointment_time ?? '-'],
        ['Doctor', appointment.doctor_name ?? 'Unassigned'],
        ['Visit Type', appointment.visit_type ?? '-'],
        ['Appointment Type', appointment.appointment_type ?? '-'],
        ['Problem Area', appointment.problem_area ?? '-'],
        ['Tooth No', appointment.tooth_no ?? '-'],
        ['Chief Complaint', appointment.chief_complaint ?? '-'],
        ['Priority', appointment.priority ?? '-'],
        ['Status', appointment.status ?? '-'],
        ['Notes', appointment.notes ?? '-'],
    ];
});

const billingRows = computed(() => {
    const billing = props.appointment?.billing ?? {};

    return [
        ['Consultation Fee', formatCurrency(billing.consultation_fee)],
        ['Treatment Estimate', formatCurrency(billing.treatment_estimate)],
        ['Discount', formatCurrency(billing.discount)],
        ['Paid Amount', formatCurrency(billing.paid_amount)],
        ['Payment Mode', billing.payment_mode || '-'],
        ['Payment Status', billing.payment_status || '-'],
        ['Remarks', billing.remarks || '-'],
    ];
});
</script>

<template>
    <Dialog v-model:visible="model" modal maximizable header="Appointment Details" :style="{ width: 'min(960px, 96vw)' }">
        <div v-if="appointment" class="space-y-6">
            <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl bg-slate-50 p-4">
                <div>
                    <h3 class="text-lg font-semibold text-slate-900">{{ appointment.appointment_no }}</h3>
                    <p class="text-sm text-slate-500">{{ appointment.appointment_date }} {{ appointment.appointment_time }}</p>
                </div>
                <div class="flex gap-2">
                    <Tag :value="appointment.status || 'Scheduled'" severity="info" />
                    <Tag :value="appointment.billing?.payment_status || 'Pending'" severity="warn" />
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <div class="rounded-2xl border border-slate-200 p-4">
                    <h4 class="mb-4 text-sm font-semibold uppercase tracking-wide text-slate-500">Appointment</h4>
                    <div class="space-y-3">
                        <div v-for="[label, value] in detailRows" :key="label" class="flex items-start justify-between gap-4 border-b border-slate-100 pb-3 text-sm last:border-b-0 last:pb-0">
                            <span class="text-slate-500">{{ label }}</span>
                            <span class="text-right font-medium text-slate-800">{{ value }}</span>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 p-4">
                    <h4 class="mb-4 text-sm font-semibold uppercase tracking-wide text-slate-500">Billing Details</h4>
                    <div class="space-y-3">
                        <div v-for="[label, value] in billingRows" :key="label" class="flex items-start justify-between gap-4 border-b border-slate-100 pb-3 text-sm last:border-b-0 last:pb-0">
                            <span class="text-slate-500">{{ label }}</span>
                            <span class="text-right font-medium text-slate-800">{{ value }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <template #footer>
            <div class="flex justify-end gap-3">
                <Button label="Print" icon="pi pi-print" outlined @click="emit('print', appointment)" />
                <Button label="Close" severity="secondary" outlined @click="model = false" />
            </div>
        </template>
    </Dialog>
</template>

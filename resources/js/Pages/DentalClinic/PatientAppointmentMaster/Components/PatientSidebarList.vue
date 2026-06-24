<script setup>
import { Link } from '@inertiajs/vue3';
import Card from 'primevue/card';
import Tag from 'primevue/tag';

defineProps({
    sidebar: {
        type: Object,
        default: () => ({
            today_appointments: [],
            upcoming_appointments: [],
            recent_patients: [],
        }),
    },
});

const statusSeverity = (status) => ({
    Completed: 'success',
    Scheduled: 'info',
    Rescheduled: 'warn',
    Cancelled: 'danger',
    'No Show': 'contrast',
    Active: 'success',
}[status] || 'secondary');
</script>

<template>
    <div class="space-y-4">
        <Card>
            <template #title>Today’s Appointments</template>
            <template #content>
                <div class="space-y-3">
                    <div v-for="item in sidebar.today_appointments" :key="`today-${item.id}`" class="rounded-xl border border-slate-200 p-3">
                        <div class="flex items-start justify-between gap-3">
                            <Link :href="route('dental-patient-appointments.show', item.patient_id)" class="font-medium text-slate-900 hover:text-sky-600">
                                {{ item.patient_name }}
                            </Link>
                            <Tag :value="item.status" :severity="statusSeverity(item.status)" />
                        </div>
                        <p class="mt-1 text-sm text-slate-500">{{ item.appointment_date }} {{ item.appointment_time }}</p>
                        <p class="text-xs text-slate-500">{{ item.doctor }}</p>
                    </div>
                    <p v-if="!sidebar.today_appointments?.length" class="text-sm text-slate-500">No appointments for today.</p>
                </div>
            </template>
        </Card>

        <Card>
            <template #title>Upcoming Appointments</template>
            <template #content>
                <div class="space-y-3">
                    <div v-for="item in sidebar.upcoming_appointments" :key="`upcoming-${item.id}`" class="rounded-xl border border-slate-200 p-3">
                        <div class="flex items-start justify-between gap-3">
                            <Link :href="route('dental-patient-appointments.show', item.patient_id)" class="font-medium text-slate-900 hover:text-sky-600">
                                {{ item.patient_name }}
                            </Link>
                            <Tag :value="item.status" :severity="statusSeverity(item.status)" />
                        </div>
                        <p class="mt-1 text-sm text-slate-500">{{ item.appointment_date }} {{ item.appointment_time }}</p>
                        <p class="text-xs text-slate-500">{{ item.doctor }}</p>
                    </div>
                    <p v-if="!sidebar.upcoming_appointments?.length" class="text-sm text-slate-500">No upcoming appointments.</p>
                </div>
            </template>
        </Card>

        <Card>
            <template #title>Recent Patients</template>
            <template #content>
                <div class="space-y-3">
                    <div v-for="item in sidebar.recent_patients" :key="`recent-${item.id}`" class="rounded-xl border border-slate-200 p-3">
                        <div class="flex items-start justify-between gap-3">
                            <Link :href="route('dental-patient-appointments.show', item.id)" class="font-medium text-slate-900 hover:text-sky-600">
                                {{ item.patient_name }}
                            </Link>
                            <Tag :value="item.status" :severity="statusSeverity(item.status)" />
                        </div>
                        <p class="mt-1 text-sm text-slate-500">{{ item.appointment_date || 'No appointment yet' }} {{ item.appointment_time || '' }}</p>
                        <p class="text-xs text-slate-500">{{ item.doctor }}</p>
                    </div>
                    <p v-if="!sidebar.recent_patients?.length" class="text-sm text-slate-500">No recent patients.</p>
                </div>
            </template>
        </Card>
    </div>
</template>

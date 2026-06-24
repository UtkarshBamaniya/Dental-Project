<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import AddNewButton from '@/Pages/Common/AugComponent/AddNewButton.vue';
import CommonDataTable from '@/Pages/Common/AugComponent/DataTable.vue';
import DeleteDialog from '@/Pages/Common/AugComponent/DeleteDialog.vue';
import Toolbar from '@/Pages/Common/AugComponent/Toolbar.vue';
import FollowUpForm from './FollowUpForm.vue';
import Form from './Form.vue';
import { appointmentStatusOptions } from './formOptions';

const props = defineProps({
    permissions: {
        type: Object,
        required: true,
    },
    title: {
        type: String,
        required: true,
    },
    desc: {
        type: String,
        default: '',
    },
    icon: {
        type: String,
        default: 'pi pi-calendar-plus',
    },
});

const flash = usePage().props.flash;
const formRef = ref(null);
const followUpRef = ref(null);
const deleteDialogRef = ref(null);
const tableRef = ref(null);

const allColumns = [
    { key: 'sequence', field: 'sequence', header: 'No', sortable: false, visible: true },
    { key: 'patient_code', field: 'patient_code', header: 'Patient Code', filterType: 'text', filterNm: 'patient_code', sortable: true, visible: true },
    { key: 'patient_name', field: 'patient_name', header: 'Patient Name', filterType: 'text', filterNm: 'patient_name', sortable: true, visible: true },
    { key: 'mobile_no', field: 'mobile_no', header: 'Mobile No', filterType: 'text', filterNm: 'mobile_no', sortable: true, visible: true },
    {
        key: 'gender',
        field: 'gender',
        header: 'Gender',
        filterType: 'select',
        filterNm: 'gender',
        filterOptions: [
            { label: 'Male', value: 'Male' },
            { label: 'Female', value: 'Female' },
            { label: 'Other', value: 'Other' },
        ],
        sortable: true,
        visible: true,
    },
    { key: 'age', field: 'age', header: 'Age', sortable: true, visible: true },
    { key: 'latest_appointment_date', field: 'latest_appointment_date', header: 'Latest Appointment Date', filterType: 'date', filterNm: 'appointment_date', sortable: true, visible: true },
    {
        key: 'latest_appointment_status',
        field: 'latest_appointment_status',
        header: 'Appointment Status',
        filterType: 'select',
        filterNm: 'appointment_status',
        filterOptions: appointmentStatusOptions.map((value) => ({ label: value, value })),
        sortable: false,
        visible: true,
    },
];

const menuItems = [
    {
        label: 'Show',
        icon: 'pi pi-eye',
        url: (data) => data?.id ? route('dental-patient-appointments.show', data.id) : null,
    },
    {
        label: 'Edit Patient',
        icon: 'pi pi-pencil',
        action: (data) => {
            if (!data?.id) {
                return;
            }

            formRef.value?.openEdit(data.id);
        },
    },
    {
        label: 'Add Follow-up',
        icon: 'pi pi-calendar-plus',
        action: (data) => {
            if (!data?.id) {
                return;
            }

            followUpRef.value?.openNew(data);
        },
    },
    {
        label: 'Delete',
        icon: 'pi pi-trash',
        action: (data) => {
            if (!data?.id) {
                return;
            }

            deleteDialogRef.value?.openDelete(data.id, 'dental-patient-appointments.destroy');
        },
    },
];
</script>

<template>
    <Head :title="title" />

    <AuthenticatedLayout>
        <div class="mb-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <Toolbar :title="title" :desc="desc" :icon="icon">
                <template #actions>
                    <AddNewButton :can-create="props.permissions.canCreate" tooltip="Add" @click="formRef?.openNew()" />
                </template>
            </Toolbar>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div
                v-if="flash.success"
                class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"
            >
                {{ flash.success }}
            </div>

            <CommonDataTable
                ref="tableRef"
                route_name="dental-patient-appointments.index"
                :allColumns="allColumns"
                :menuItems="menuItems"
                :permissions="props.permissions"
                moduleNm="patient_appointments"
            />
        </div>

        <DeleteDialog ref="deleteDialogRef" module-name="patient" @fetch-data="tableRef?.fetchData()" />
        <Form ref="formRef" @fetch-data="tableRef?.fetchData()" />
        <FollowUpForm ref="followUpRef" @fetch-data="tableRef?.fetchData()" />
    </AuthenticatedLayout>
</template>

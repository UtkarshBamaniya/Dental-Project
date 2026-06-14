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
import Show from './Show.vue';
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
const showRef = ref(null);
const followUpRef = ref(null);
const deleteDialogRef = ref(null);
const tableRef = ref(null);

const allColumns = [
    {
        key: 'sequence',
        field: 'sequence',
        header: 'No',
        sortable: false,
        visible: true,
    },
    {
        key: 'patient_code',
        field: 'patient_code',
        header: 'Patient Code',
        filterType: 'text',
        filterNm: 'search',
        sortable: true,
        visible: true,
    },
    {
        key: 'patient_name',
        field: 'patient_name',
        header: 'Patient Name',
        filterType: 'text',
        filterNm: 'patient_name',
        sortable: true,
        visible: true,
    },
    {
        key: 'mobile_no',
        field: 'mobile_no',
        header: 'Mobile No',
        filterType: 'text',
        filterNm: 'mobile_no',
        sortable: true,
        visible: true,
    },
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
    {
        key: 'age',
        field: 'age',
        header: 'Age',
        filterType: 'number',
        filterNm: 'age',
        sortable: true,
        visible: true,
    },
    {
        key: 'appointment_date',
        field: 'latest_appointment_date',
        header: 'Appointment Date',
        filterType: 'text',
        filterNm: 'appointment_date',
        sortable: true,
        visible: true,
    },
    {
        key: 'appointment_status',
        field: 'latest_appointment_status',
        header: 'Appointment Status',
        filterType: 'select',
        filterNm: 'appointment_status',
        filterOptions: appointmentStatusOptions.map((value) => ({
            label: value,
            value,
        })),
        sortable: false,
        visible: true,
    },
];

const menuItems = [
    {
        label: 'Show',
        icon: 'pi pi-eye',
        action: (data) => showRef.value?.openShow(data.id),
    },
    {
        label: 'Edit',
        icon: 'pi pi-pencil',
        action: (data) => formRef.value?.openEdit(data.id),
    },
    {
        label: 'Add Follow-up',
        icon: 'pi pi-calendar-plus',
        action: (data) => followUpRef.value?.openNew(data),
    },
    {
        label: 'Delete',
        icon: 'pi pi-trash',
        action: (data) => deleteDialogRef.value?.openDelete(data.id, 'dental-patient-appointments.destroy'),
    },
];
</script>

<template>
    <!-- <Head title="Patient Appointment " /> -->
    <AuthenticatedLayout>
        <div class="card !border-0 !border-slate-100 shadow-sm mb-4">
            <Toolbar :title="title" :desc="desc" :icon="icon">
                <template #actions>
                    <AddNewButton
                        :can-create="props.permissions.canCreate"
                        tooltip="Add"
                        @click="formRef?.openNew()"
                    />
                </template>
            </Toolbar>
        </div>

        <div class="card !border-0 !border-slate-100 shadow-sm">
            <div
                v-if="flash.success"
                class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 mb-4"
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

        <DeleteDialog
            ref="deleteDialogRef"
            module-name="patient appointment"
            @fetch-data="tableRef?.fetchData()"
        />
        <Form ref="formRef" @fetch-data="tableRef?.fetchData()" />
        <FollowUpForm ref="followUpRef" @fetch-data="tableRef?.fetchData()" />
        <Show ref="showRef" />
    </AuthenticatedLayout>
</template>

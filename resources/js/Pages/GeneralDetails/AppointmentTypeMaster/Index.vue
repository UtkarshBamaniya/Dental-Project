<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AddNewButton from '@/Pages/Common/AugComponent/AddNewButton.vue';
import CommonDataTable from '@/Pages/Common/AugComponent/DataTable.vue';
import DeleteDialog from '@/Pages/Common/AugComponent/DeleteDialog.vue';
import Toolbar from '@/Pages/Common/AugComponent/Toolbar.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import Form from './Form.vue';

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
        default: 'pi pi-calendar',
    },
});

const flash = usePage().props.flash;
const formRef = ref(null);
const tableRef = ref(null);
const deleteDialogRef = ref(null);

const statusOptions = [
    { label: 'Active', value: 1 },
    { label: 'Inactive', value: 0 },
];

const statusBadge = (label, active) =>
    `<span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium ${
        active
            ? 'bg-emerald-100 text-emerald-700'
            : 'bg-slate-200 text-slate-600'
    }">${label}</span>`;

const allColumns = [
    { key: 'no', field: 'no', header: 'No', sortable: false, visible: true },
    { key: 'name', field: 'name', header: 'Name', filterType: 'text', filterNm: 'name', sortable: true, visible: true },
    { key: 'description', field: 'description', header: 'Description', filterType: 'text', filterNm: 'description', sortable: true, visible: true },
    {
        key: 'status',
        field: 'status_label',
        header: 'Status',
        filterType: 'select',
        filterNm: 'status',
        filterOptions: statusOptions,
        sortable: true,
        visible: true,
        customRender: (data) => statusBadge(data.status_label, Number(data.status) === 1),
    },
];

const menuItems = [
    {
        label: 'Edit',
        icon: 'pi pi-pencil',
        action: (data) => {
            if (!data?.id) {
                return;
            }

            formRef.value?.openEdit(data.id);
        },
    },
    {
        label: 'Delete',
        icon: 'pi pi-trash',
        action: (data) => {
            if (!data?.id) {
                return;
            }

            deleteDialogRef.value?.openDelete(data.id, 'appointment-type.destroy');
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
            <div
                v-if="flash.error"
                class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700"
            >
                {{ flash.error }}
            </div>

            <CommonDataTable
                ref="tableRef"
                route_name="appointment-type.index"
                :allColumns="allColumns"
                :menuItems="menuItems"
                :permissions="props.permissions"
                moduleNm="appointment_types"
            />
        </div>

        <DeleteDialog ref="deleteDialogRef" module-name="appointment type" @fetch-data="tableRef?.fetchData()" />
        <Form ref="formRef" @fetch-data="tableRef?.fetchData()" />
    </AuthenticatedLayout>
</template>

<script setup>
const sales = [
    { customer: 'Olivia Martin', company: 'Northwind', amount: 1200, status: 'Paid' },
    { customer: 'James Smith', company: 'BlueOcean', amount: 860, status: 'Pending' },
    { customer: 'Emma Wilson', company: 'Apex Labs', amount: 640, status: 'Paid' },
    { customer: 'Liam Johnson', company: 'Vertex', amount: 430, status: 'Refunded' },
    { customer: 'Sophia Brown', company: 'Sunrise Co.', amount: 390, status: 'Paid' },
];

const formatCurrency = (value) =>
    new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(value);

const severity = (status) => ({
    Paid: 'success',
    Pending: 'warn',
    Refunded: 'danger',
}[status] ?? 'info');
</script>

<template>
    <div class="card">
        <div class="mb-4 text-xl font-semibold">Recent Sales</div>

        <DataTable :value="sales" :rows="5" paginator responsive-layout="scroll">
            <Column field="customer" header="Customer" />
            <Column field="company" header="Company" />
            <Column header="Amount">
                <template #body="{ data }">
                    {{ formatCurrency(data.amount) }}
                </template>
            </Column>
            <Column header="Status">
                <template #body="{ data }">
                    <Tag :severity="severity(data.status)" :value="data.status" />
                </template>
            </Column>
        </DataTable>
    </div>
</template>

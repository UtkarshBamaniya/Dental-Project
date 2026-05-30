<script setup>
const notifications = [
    { title: 'Deployment finished', detail: 'Production release 1.3.8 completed 18 minutes ago.' },
    { title: 'Invoices synced', detail: '42 invoices were reconciled from the billing provider.' },
    { title: 'New team member', detail: 'A new workspace admin was invited to the project.' },
    { title: 'Storage threshold', detail: 'Media storage has reached 78% of the monthly allowance.' },
];
</script>

<template>
    <div class="card">
        <div class="mb-4 text-xl font-semibold">Notifications</div>

        <div class="space-y-4">
            <div
                v-for="notice in notifications"
                :key="notice.title"
                class="flex items-start gap-3 border-b border-slate-100 pb-4 last:border-b-0 last:pb-0"
            >
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-slate-600">
                    <i class="pi pi-bell"></i>
                </div>
                <div>
                    <div class="font-medium text-slate-900">{{ notice.title }}</div>
                    <p class="mt-1 text-sm text-slate-500">{{ notice.detail }}</p>
                </div>
            </div>
        </div>
    </div>
</template>

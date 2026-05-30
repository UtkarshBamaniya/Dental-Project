<script setup>
import NotificationsWidget from '@/components/dashboard/NotificationsWidget.vue';
import RecentSalesWidget from '@/components/dashboard/RecentSalesWidget.vue';
import RevenueStreamWidget from '@/components/dashboard/RevenueStreamWidget.vue';
import StatsWidget from '@/components/dashboard/StatsWidget.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <div class="mb-6 flex items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-semibold text-slate-900">Dashboard</h1>
                <p class="mt-1 text-sm text-slate-500">
                    Sakai layout adapted for Laravel, Inertia, and Vue 3.
                </p>
            </div>
        </div>

        <div class="grid grid-cols-12 gap-8">
            <StatsWidget />

            <div class="col-span-12 xl:col-span-7">
                <RevenueStreamWidget />
            </div>

            <div class="col-span-12 xl:col-span-5">
                <NotificationsWidget />
            </div>

            <div class="col-span-12">
                <RecentSalesWidget />
            </div>
        </div>
    </AuthenticatedLayout>
</template>

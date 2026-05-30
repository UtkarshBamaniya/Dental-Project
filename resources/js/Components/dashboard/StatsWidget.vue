<script setup>
const stats = [
    {
        label: 'Orders',
        value: '152',
        accent: 'bg-blue-100 text-blue-500',
        icon: 'pi-shopping-cart',
        delta: '24 new since last visit',
    },
    {
        label: 'Revenue',
        value: '$2,100',
        accent: 'bg-orange-100 text-orange-500',
        icon: 'pi-dollar',
        delta: '52% up from last week',
    },
    {
        label: 'Customers',
        value: '28,441',
        accent: 'bg-cyan-100 text-cyan-500',
        icon: 'pi-users',
        delta: '520 newly registered',
    },
    {
        label: 'Comments',
        value: '152 unread',
        accent: 'bg-purple-100 text-purple-500',
        icon: 'pi-comment',
        delta: '85 responded today',
    },
];
</script>

<template>
    <div
        v-for="stat in stats"
        :key="stat.label"
        class="col-span-12 lg:col-span-6 xl:col-span-3"
    >
        <div class="card mb-0">
            <div class="mb-4 flex items-start justify-between">
                <div>
                    <span class="mb-3 block text-sm font-medium text-slate-500">{{ stat.label }}</span>
                    <div class="text-xl font-semibold text-slate-900">{{ stat.value }}</div>
                </div>
                <div
                    class="flex h-10 w-10 items-center justify-center rounded-full"
                    :class="stat.accent"
                >
                    <i class="pi text-lg" :class="stat.icon"></i>
                </div>
            </div>
            <span class="text-sm text-slate-500">{{ stat.delta }}</span>
        </div>
    </div>
</template>

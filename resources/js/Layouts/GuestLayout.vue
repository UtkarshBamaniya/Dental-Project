<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center pt-6 sm:justify-center sm:pt-0"
        style="background-image: url('/Uploads/smile-bg.png'); background-size: cover; background-position: center; background-repeat: no-repeat;"
    >
        <!-- Added glassmorphism effect to match the background -->
        <div
            class="mt-6 w-full overflow-hidden px-8 py-10 shadow-2xl sm:max-w-md sm:rounded-2xl border border-white/50"
            style="background: rgba(255, 255, 255, 0.65); backdrop-filter: blur(16px);"
        >
            <slot />
        </div>
    </div>
</template>

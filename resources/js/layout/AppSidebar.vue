<script setup>
import { onBeforeUnmount, ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import AppMenu from './AppMenu.vue';
import { useLayout } from './composables/layout';

const page = usePage();
const { closeMenu, isDesktop, layoutState } = useLayout();
const sidebarRef = ref(null);
let outsideClickListener = null;

watch(
    () => page.url,
    () => {
        layoutState.activeMenuKey = null;
        closeMenu();
    },
    { immediate: true },
);

watch(
    () => layoutState.overlayMenuActive,
    (active) => {
        if (!isDesktop()) {
            return;
        }

        if (active) {
            bindOutsideClickListener();
            return;
        }

        unbindOutsideClickListener();
    },
);

const bindOutsideClickListener = () => {
    if (outsideClickListener) {
        return;
    }

    outsideClickListener = (event) => {
        const topbarButton = document.querySelector('.layout-menu-button');

        if (
            sidebarRef.value &&
            !sidebarRef.value.contains(event.target) &&
            !topbarButton?.contains(event.target)
        ) {
            layoutState.overlayMenuActive = false;
        }
    };

    document.addEventListener('click', outsideClickListener);
};

const unbindOutsideClickListener = () => {
    if (!outsideClickListener) {
        return;
    }

    document.removeEventListener('click', outsideClickListener);
    outsideClickListener = null;
};

onBeforeUnmount(() => {
    unbindOutsideClickListener();
});
</script>

<template>
    <div ref="sidebarRef" class="layout-sidebar">
        <AppMenu />
    </div>
</template>

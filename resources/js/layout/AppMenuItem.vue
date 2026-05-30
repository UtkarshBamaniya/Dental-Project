<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useLayout } from './composables/layout';

const props = defineProps({
    item: {
        type: Object,
        required: true,
    },
    index: {
        type: Number,
        default: 0,
    },
    root: {
        type: Boolean,
        default: true,
    },
    parentKey: {
        type: String,
        default: '',
    },
});

const page = usePage();
const { closeMenu, layoutState } = useLayout();

const itemKey = computed(() => `${props.parentKey}${props.parentKey ? '-' : ''}${props.index}`);

const hasActiveChild = (item) =>
    (item.items ?? []).some((child) => {
        if (child.href && page.url.startsWith(child.href)) {
            return true;
        }

        return hasActiveChild(child);
    });

const isActive = computed(() => {
    if (props.item.items?.length) {
        return layoutState.activeMenuKey === itemKey.value || hasActiveChild(props.item);
    }

    if (props.item.href) {
        return page.url === props.item.href || page.url.startsWith(`${props.item.href}/`);
    }

    return false;
});

const handleClick = () => {
    if (props.item.items?.length) {
        layoutState.activeMenuKey = layoutState.activeMenuKey === itemKey.value ? null : itemKey.value;
        return;
    }

    closeMenu();
};
</script>

<template>
    <li :class="{ 'layout-root-menuitem': root, 'active-menuitem': isActive }">
        <div v-if="root" class="layout-menuitem-root-text">{{ item.label }}</div>

        <a
            v-if="item.items?.length && !root"
            href="#"
            :class="item.class"
            @click.prevent="handleClick"
        >
            <i :class="item.icon" class="layout-menuitem-icon" />
            <span class="layout-menuitem-text">{{ item.label }}</span>
            <i class="pi pi-fw pi-angle-down layout-submenu-toggler" />
        </a>

        <Link
            v-else-if="item.href"
            :href="item.href"
            :class="item.class"
            class="active-route"
            @click="handleClick"
        >
            <i :class="item.icon" class="layout-menuitem-icon" />
            <span class="layout-menuitem-text">{{ item.label }}</span>
        </Link>

        <a
            v-else
            :href="item.url"
            :target="item.target"
            rel="noopener noreferrer"
            :class="item.class"
            @click="handleClick"
        >
            <i :class="item.icon" class="layout-menuitem-icon" />
            <span class="layout-menuitem-text">{{ item.label }}</span>
        </a>

        <Transition v-if="item.items?.length" name="layout-submenu">
            <ul v-show="root || isActive" class="layout-submenu">
                <AppMenuItem
                    v-for="(child, childIndex) in item.items"
                    :key="`${child.label}-${childIndex}`"
                    :item="child"
                    :index="childIndex"
                    :root="false"
                    :parent-key="itemKey"
                />
            </ul>
        </Transition>
    </li>
</template>

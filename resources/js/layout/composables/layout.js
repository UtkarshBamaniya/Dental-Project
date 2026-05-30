import { computed, reactive } from 'vue';

const layoutConfig = reactive({
    darkTheme: false,
    menuMode: 'static',
});

const layoutState = reactive({
    staticMenuInactive: false,
    overlayMenuActive: false,
    mobileMenuActive: false,
    activeMenuKey: null,
});

export function useLayout() {
    const executeDarkModeToggle = () => {
        layoutConfig.darkTheme = !layoutConfig.darkTheme;
        document.documentElement.classList.toggle('app-dark', layoutConfig.darkTheme);
    };

    const toggleDarkMode = () => {
        if (typeof document.startViewTransition !== 'function') {
            executeDarkModeToggle();
            return;
        }

        document.startViewTransition(() => executeDarkModeToggle());
    };

    const isDesktop = () => window.innerWidth > 991;

    const toggleMenu = () => {
        if (isDesktop()) {
            if (layoutConfig.menuMode === 'overlay') {
                layoutState.overlayMenuActive = !layoutState.overlayMenuActive;
                return;
            }

            layoutState.staticMenuInactive = !layoutState.staticMenuInactive;
            return;
        }

        layoutState.mobileMenuActive = !layoutState.mobileMenuActive;
    };

    const hideMobileMenu = () => {
        layoutState.mobileMenuActive = false;
        layoutState.overlayMenuActive = false;
    };

    const closeMenu = () => {
        layoutState.mobileMenuActive = false;
        layoutState.overlayMenuActive = false;
    };

    return {
        closeMenu,
        hideMobileMenu,
        isDarkTheme: computed(() => layoutConfig.darkTheme),
        isDesktop,
        layoutConfig,
        layoutState,
        toggleDarkMode,
        toggleMenu,
    };
}

import { onMounted, ref } from 'vue';

type Appearance = 'light';

export function updateTheme() {
    if (typeof window === 'undefined') {
        return;
    }

    // Always force light mode
    document.documentElement.classList.remove('dark');
}

export function initializeTheme() {
    if (typeof window === 'undefined') {
        return;
    }

    // Always force light mode
    updateTheme();
}

const appearance = ref<Appearance>('light');

export function useAppearance() {
    onMounted(() => {
        // Always ensure light mode
        appearance.value = 'light';
        updateTheme();
    });

    return {
        appearance,
    };
}

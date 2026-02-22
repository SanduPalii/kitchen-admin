import type { ComputedRef, Ref } from 'vue';
import { computed, onMounted, ref } from 'vue';
import type { Appearance, ResolvedAppearance } from '@/types';

export type { Appearance, ResolvedAppearance };

export type UseAppearanceReturn = {
    appearance: Ref<Appearance>;
    resolvedAppearance: ComputedRef<ResolvedAppearance>;
    updateAppearance: (value: Appearance) => void;
};

export function updateTheme(value: Appearance): void {
    if (typeof window === 'undefined') {
        return;
    }
    // Always force light — dark mode is disabled
    document.documentElement.classList.remove('dark');
}

export function initializeTheme(): void {
    if (typeof window === 'undefined') {
        return;
    }
    // Force light theme, clear any stored dark/system preference
    localStorage.setItem('appearance', 'light');
    document.cookie = 'appearance=light;path=/;max-age=31536000;SameSite=Lax';
    document.documentElement.classList.remove('dark');
}

const appearance = ref<Appearance>('light');

export function useAppearance(): UseAppearanceReturn {
    onMounted(() => {
        appearance.value = 'light';
    });

    const resolvedAppearance = computed<ResolvedAppearance>(() => 'light');

    function updateAppearance(_value: Appearance) {
        // Theme switching is disabled — always light
        appearance.value = 'light';
    }

    return {
        appearance,
        resolvedAppearance,
        updateAppearance,
    };
}

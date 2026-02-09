import { createInertiaApp } from '@inertiajs/vue3'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import Aura from '@primeuix/themes/lara'
import PrimeVue from 'primevue/config'
import ConfirmationService from 'primevue/confirmationservice'
import ConfirmDialog from 'primevue/confirmdialog'
import 'primeicons/primeicons.css'
import type { DefineComponent } from 'vue'
import { createApp, h } from 'vue'
import '../css/app.css'
import { initializeTheme } from './composables/useAppearance'

const appName = import.meta.env.VITE_APP_NAME || 'Laravel'

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) =>
        resolvePageComponent(
            `./pages/${name}.vue`,
            import.meta.glob<DefineComponent>('./pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        const vueApp = createApp({ render: () => h(App, props) })

        vueApp
            .use(plugin)
            .use(PrimeVue, {
                theme: {
                    preset: Aura,
                    options: {
                        prefix: 'p',
                        darkModeSelector: 'light',
                        cssLayer: false,
                    },
                },
            })
            .use(ConfirmationService)
            .component('ConfirmDialog', ConfirmDialog)
            .mount(el)
    },
    progress: {
        color: '#4B5563',
    },
})

// This will set light / dark mode on page load...
initializeTheme()

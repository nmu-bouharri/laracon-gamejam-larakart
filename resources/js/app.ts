import "./bootstrap";
import "../css/app.css";

import { createApp, type DefineComponent, h } from "vue";
import { createInertiaApp } from "@inertiajs/vue3";
import { ZiggyVue } from "ziggy-js";

// Import global components
import AppLayout from "./Layouts/AppLayout.vue";
// Multiplayer functionality removed - focusing on AI racing

// Create the Inertia app
createInertiaApp({
    title: () => "Larakart",

    // Resolve the initial page component
    resolve: async (name) => {
        const pages = import.meta.glob("./Pages/**/*.vue", {
            eager: true
        })

        let page = await pages[`./Pages/${name}.vue`] as DefineComponent
        if (typeof page.default.layout !== 'boolean') {
            page.default.layout = page.default.layout || AppLayout
        }

        return pages[`./Pages/${name}.vue`] as Promise<DefineComponent>
    },

    // Setup the app
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });

        // Use plugins
        app.use(plugin).use(ZiggyVue).mount(el);
    },

    // Progress bar settings
    progress: {
        color: "#f59e0b", // yellow-500
        showSpinner: true,
    },
});

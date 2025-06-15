import { createApp, h } from "vue";
import { createInertiaApp } from "@inertiajs/vue3";
//import MainLayout from "./Layouts/MainLayout.vue";

// Vuetify
import "@mdi/font/css/materialdesignicons.css";
import "vuetify/styles";
import { createVuetify } from "vuetify";

createInertiaApp({
    resolve: (name) => {
        const pages = import.meta.glob("./Pages/**/*.vue", { eager: true });
        let page = pages[`./Pages/${name}.vue`];
        // page.default.layout = page.default.layout || MainLayout;
        return page;
    },
    setup({ el, App, props, plugin }) {
        const vuetify = createVuetify();
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(vuetify)
            .mount(el);
    },
});

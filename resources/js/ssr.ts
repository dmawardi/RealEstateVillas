import { createInertiaApp } from '@inertiajs/vue3';
import createServer from '@inertiajs/vue3/server';
import { renderToString } from '@vue/server-renderer';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createSSRApp, DefineComponent, h } from 'vue';
import { ZiggyVue } from 'ziggy-js';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createServer((page) => {
    console.log(`SSR Rendering: ${page.component} for URL: ${page.url}`);
    return createInertiaApp({
        page,
        render: renderToString,
        title: (title) => title ? `${title} - ${appName}` : appName,
        resolve: resolvePage,
        setup: ({ App, props, plugin }) => {
            const ziggyConfig = page.props.ziggy as any;

            // 1. CREATE A SAFETY MOCK
            // This prevents the "TypeError: route is not defined" or "Cannot read contact"
            // if Ziggy fails to load properly.
            const globalRoute = (name?: string) => {
                try {
                    // Try to use the actual Ziggy logic if available
                    return (globalThis as any).Ziggy ? (globalThis as any).route(name) : `#${name}`;
                } catch (e) {
                    return `#${name}:${e}`; // Fallback to a hash link so it doesn't crash
                }
            };
            
            // Set the global route function
            (globalThis as any).route = globalRoute;
            const app = createSSRApp({ render: () => h(App, props) })
                .use(plugin);
            
            // 2. Attempt to load Ziggy but don't die if fails
            if (ziggyConfig) {
                app.use(ZiggyVue, {
                    ...ziggyConfig,
                    location: new URL(ziggyConfig.location),
                });
            }
            return app;
        }   
    });
}, { cluster: true });

function resolvePage(name: string) {
    const pages = import.meta.glob<DefineComponent>('./pages/**/*.vue');

    return resolvePageComponent<DefineComponent>(`./pages/${name}.vue`, pages);
}

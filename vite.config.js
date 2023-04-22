import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue'
import graphql from "@rollup/plugin-graphql";
import { quasar, transformAssetUrls } from '@quasar/vite-plugin'
import { VitePWA } from 'vite-plugin-pwa'

export default defineConfig({
    plugins: [
        vue({
            template: { transformAssetUrls }
        }),
        VitePWA({
            registerType: 'autoUpdate',
            strategies: 'injectManifest',
            injectRegister: false,
            srcDir: 'resources/js/',
            filename: 'service-worker.js',
            outDir: 'public',
            injectManifest: {
                injectionPoint: undefined
            },
            workbox: {
                // Only precache these files - html should be excluded
                globPatterns: ['**/*.{js,css,ico,png,svg}'],
                // Don't fallback on document based (e.g. `/some-page`) requests
                // Even though this says `null` by default, I had to set this specifically to `null` to make it work
                navigateFallback: null,
                swDest: "service-worker.js"
            },
            manifest: {
                "name": process.env.APP_NAME,
                "short_name": process.env.APP_SHORTNAME,
                "description": process.env.APP_DESCRIPTION,
                "theme_color": "#ffffff",
                "display": "standalone",
                "icons": [
                    {
                        "src": "pwa-192x192.png",
                        "sizes": "192x192",
                        "type": "image/png"
                    },
                    {
                        "src": "pwa-512x512.png",
                        "sizes": "512x512",
                        "type": "image/png"
                    }
                ]
            },
            devOptions: {
                enabled: process.env.APP_DEBUG === 'true',
                /* when using generateSW the PWA plugin will switch to classic */
                type: 'module',
            },
        }),
        graphql(),
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        quasar({
            sassVariables: 'resources/css/quasar-variables.sass'
        })
    ],
});

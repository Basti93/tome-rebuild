import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue'
import graphql from "@rollup/plugin-graphql";
import { quasar, transformAssetUrls } from '@quasar/vite-plugin'

export default defineConfig({
    plugins: [
        vue({
            template: { transformAssetUrls }
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

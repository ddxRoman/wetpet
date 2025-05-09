import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/main.css',
                'resources/sass/app.scss',
                'resources/js/app.js',
                'resources/js/script.js',
            ],
            refresh: true,
        }),
    ],
});

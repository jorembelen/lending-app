import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/sass/app.scss',
                'resources/css/tailwind.css',
                'resources/js/app.js',
                'resources/js/qr-scanner.js',
                'resources/js/collector/index.js',
            ],
            refresh: true,
        }),
    ],
});

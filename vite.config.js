import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/user/base.css',
                'resources/css/user/reserve.css',
                'resources/css/user/home.css',
                'resources/js/app.js',
                'resources/js/user/reserve.js',
                'resources/js/user/home.js',
                'resources/css/admin/calendar.css',
                'resources/js/admin/calendar.js',
            ],
            refresh: true,
        }),
    ],
});

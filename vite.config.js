import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css'
                , 'resources/css/admin/calendar.css'
                , 'resources/js/admin/calendar.js'
            ],
            refresh: true,
        }),
    ],
});

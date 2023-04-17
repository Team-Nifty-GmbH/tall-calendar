import {defineConfig} from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/js/index.js',
                'resources/js/calendar.js',
                'resources/js/fullcalendar.js',
                'resources/css/calendar.css',
            ],
            publicDirectory: 'dist',
            buildDirectory: 'build',
            refresh: true
        }),
    ],
});

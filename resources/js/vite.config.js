import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    // Agar HMR (auto refresh) berfungsi dari dalam container Sail
    server: {
        host: '0.0.0.0',
        hmr: { host: 'localhost' },
    },
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
});
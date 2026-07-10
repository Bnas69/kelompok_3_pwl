import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
    ],
    build: {
        rollupOptions: {
            output: {
                manualChunks: (id) => {
                    if (id.includes('bootstrap')) return 'vendor';
                    if (id.includes('chart.js')) return 'vendor';
                },
            },
        },
    },
    server: {
        host: '0.0.0.0',
        port: 5173,
    },
});

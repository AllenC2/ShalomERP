import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/sass/app.scss',
                'resources/sass/rainbow.scss',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
    ],
    build: {
        manifest: 'manifest.json',
        outDir: 'public/build',
        rollupOptions: {
            input: [
                'resources/sass/app.scss',
                'resources/sass/rainbow.scss',
                'resources/js/app.js'
            ]
        }
    }
});

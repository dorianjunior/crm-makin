import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/tailwind.css',  // Tailwind CSS v4 (pure CSS for @source directives)
                'resources/scss/app.scss',     // Custom SCSS styles
                'resources/js/app.js'
            ],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        tailwindcss(),
    ],
    css: {
        preprocessorOptions: {
            scss: {
                api: 'modern-compiler',
                additionalData: `@use "sass:math";`,
            }
        }
    },
    build: {
        // Production optimizations
        target: 'es2020',
        cssCodeSplit: true,
        minify: 'esbuild',
        rollupOptions: {
            output: {
                manualChunks: {
                    'vendor': ['vue', '@inertiajs/vue3'],
                    'fontawesome': ['@fortawesome/fontawesome-free'],
                },
            },
        },
        // Improve build performance
        chunkSizeWarningLimit: 1000,
        reportCompressedSize: false,
    },
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
        // Better HMR performance
        hmr: {
            overlay: true,
        },
    },
    optimizeDeps: {
        include: ['vue', '@inertiajs/vue3'],
    },
    resolve: {
        alias: {
            '@': '/resources/js',
        },
    },
});

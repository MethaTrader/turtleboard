import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                // CSS files
                'resources/css/app.css',
                'resources/css/referral-network.css',
                'resources/css/animations.css',

                // Main JS files
                'resources/js/app.js',
                'resources/js/charts.js',
                'resources/js/onboarding.js',

                // Network visualization entry point
                'resources/js/interactive-referral-network.js'
            ],
            refresh: true,
        }),
    ],
    resolve: {
        alias: {
            '@': '/resources/js',
            '@modules': '/resources/js/modules',
            '@css': '/resources/css'
        }
    },
    build: {
        rollupOptions: {
            output: {
                manualChunks: {
                    // Separate vis-network libraries for better caching
                    'vis-network': ['vis-network', 'vis-data'],

                    // Group network modules together
                    'network-core': [
                        './resources/js/modules/utils.js',
                        './resources/js/modules/network-config.js',
                        './resources/js/modules/app-config.js'
                    ],

                    // Group UI and interaction modules
                    'network-ui': [
                        './resources/js/modules/ui-components.js',
                        './resources/js/modules/event-manager.js'
                    ],

                    // Group data management modules
                    'network-data': [
                        './resources/js/modules/data-service.js',
                        './resources/js/modules/node-manager.js',
                        './resources/js/modules/edge-manager.js'
                    ],

                    // Performance optimization as separate chunk
                    'network-performance': [
                        './resources/js/modules/performance-optimizer.js'
                    ]
                }
            }
        },
        target: 'es2015', // Support for older browsers
        sourcemap: true   // Enable source maps for debugging
    },
    optimizeDeps: {
        include: [
            'vis-network',
            'vis-data'
        ]
    },
    server: {
        hmr: {
            overlay: false
        }
    }
});
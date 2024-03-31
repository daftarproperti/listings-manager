import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/js/app.tsx', 'resources/css/app.css'],
            ssr: 'resources/js/ssr.tsx',
            refresh: true,
        }),
        react(),
    ],
    server: {
        // For development behind reverse proxy, set these VITE_HMR_* environment variables
        hmr: {
            host: process.env.VITE_HMR_HOST || 'localhost',
            clientPort: process.env.VITE_HMR_CLIENT_PORT ? parseInt(process.env.VITE_HMR_CLIENT_PORT, 10) : 5173,
            protocol: process.env.VITE_HMR_PROTOCOL || 'ws',
        },
    },
});

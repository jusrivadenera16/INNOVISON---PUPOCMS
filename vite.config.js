import { defineConfig } from 'vite';

export default defineConfig({
    publicDir: false,
    build: {
        manifest: false,
        outDir: 'public',
        emptyOutDir: false,
        rollupOptions: {
            input: {
                app: 'resources/js/app.js',
                styles: 'resources/css/app.css',
            },
            output: {
                entryFileNames: 'js/[name].js',
                assetFileNames: (asset) => asset.name?.endsWith('.css')
                    ? 'css/app.css'
                    : 'assets/[name][extname]',
            },
        },
    },
});

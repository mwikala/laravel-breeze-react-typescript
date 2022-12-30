import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';
import { exec } from 'child_process';

export default defineConfig({
    plugins: [
        laravel({
            input: 'resources/js/app.tsx',
            refresh: true,
        }),
        react(),
        ziggy(),
    ],
});

function ziggy(paths: Array<string> = ["routes/**/*.php"], enabled: boolean = true): import('vite').Plugin {
    const generate = () => {
        exec('php artisan ziggy:typescript', (_, stdout, __) => console.log(stdout));
    }
    return {
        name: 'ziggy-vite-plugin',

        async buildStart() {
            if (! enabled) {
                return;
            }
            (await import ('chokidar')).watch(paths).on('change', (path) => {
                console.log(`Ziggy: ${path} changed, regenerating...`);
                generate();
            })
        }
    }
}

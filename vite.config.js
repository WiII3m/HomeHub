import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        })
    ],
    build: {
        // Compatibilité Legacy - transpilation ES5 stricte
        target: 'es5',
        minify: 'terser',
        terserOptions: {
            ecma: 5,
            compress: {
                arrows: false,
                pure_getters: true
            },
            output: {
                ecma: 5
            }
        }
    },
    esbuild: {
        // Force la transpilation ES5 avec désactivation des features modernes
        target: 'es5',
        supported: {
            'object-rest-spread': false,
            'optional-catch-binding': false
        }
    }
});

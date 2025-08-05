import { defineConfig } from 'vitest/config'

export default defineConfig({
  test: {
    environment: 'jsdom',  // Simule le DOM pour localStorage, document, window
    globals: true,         // describe, it, expect disponibles globalement
    setupFiles: ['./test-setup.js'], // Setup des matchers DOM
    include: ['app/Widgets/**/tests/**/*.{test,spec}.{js,mjs,cjs,ts,mts,cts,jsx,tsx}']
  }
})
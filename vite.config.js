import { defineConfig } from 'vite';

// Relative base so the build works on GitHub Pages, Netlify or any sub-folder.
export default defineConfig({
  base: './',
  build: { chunkSizeWarningLimit: 1200 },
});

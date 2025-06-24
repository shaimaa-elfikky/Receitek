// vite.config.js
import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";

// REMOVE this line: import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
  plugins: [
    laravel({
      input: ["resources/css/app.css", "resources/js/app.js"],
      refresh: true,
    }),
    // REMOVE this line: tailwindcss(),
  ],
});
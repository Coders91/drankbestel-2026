import { defineConfig } from 'vite'
import tailwindcss from '@tailwindcss/vite';
import laravel from 'laravel-vite-plugin'
import { wordpressPlugin, wordpressThemeJson } from '@roots/vite-plugin';

export default defineConfig((config) => ({
  base:
    config.command === 'build'
    ? '/wp-content/themes/drankbestel-new/public/build/' // Production (server)
    : '/app/themes/drankbestel-new/public/build/', // Development (local)
  plugins: [
    tailwindcss(),
    laravel({
      input: [
        // CSS
        'resources/css/app.css',
        'resources/css/editor.css',
        'resources/css/lib/swiper-bundle.min.css',

        // JS
        'resources/js/app.js',
        'resources/js/editor.js',
        'resources/js/lib/swiper-bundle.min.js',
      ],
      refresh: true,
    }),

    wordpressPlugin(),

    // Generate the theme.json file in the public/build/assets directory
    // based on the Tailwind config and the theme.json file from base theme folder
    wordpressThemeJson({
      disableTailwindColors: false,
      disableTailwindFonts: false,
      disableTailwindFontSizes: false,
    }),
  ],
  resolve: {
    alias: {
      '@scripts': '/resources/js',
      '@styles': '/resources/css',
      '@fonts': '/resources/fonts',
      '@images': '/resources/images',
    },
  },
}));

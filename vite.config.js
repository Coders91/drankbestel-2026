import fs from 'fs';
import { defineConfig } from 'vite'
import tailwindcss from '@tailwindcss/vite';
import laravel from 'laravel-vite-plugin'
import { wordpressPlugin, wordpressThemeJson } from '@roots/vite-plugin';

const host = 'local.drankbestel.nl';
const certsPath = '/etc/apache2/ssl';

export default defineConfig(({ command }) => {

  const config = {
    base:
      command === 'build'
      ? '/wp-content/themes/drankbestel-new/public/build/' // Production (server)
      : '/app/themes/drankbestel-new/public/build/', // Development (local)
    plugins: [
      tailwindcss(),
      laravel({
        input: [
          // CSS
          'resources/css/app.css',
          'resources/css/editor.css',

          // JS
          'resources/js/app.js',
          'resources/js/editor.js',
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
  };

  if (command === 'serve') {
    config.server = {
      host: '0.0.0.0',
      port: 5173,
      https: {
        key: fs.readFileSync(`${certsPath}/local.drankbestel.nl-key.pem`),
        cert: fs.readFileSync(`${certsPath}/local.drankbestel.nl.pem`),
      },
      hmr: {
        host: host,
      },
      cors: {
        origin: `https://${host}`,
      },
    };
  }

  return config;

});

// Vue Blocks — Vite build configuration (opt-in per Constitution Principle V).
//
// Emits a hashed JS bundle and a hashed CSS bundle into `dist/`, alongside
// a JSON manifest that `functions.php` reads when the `VB_USE_BUNDLED_ASSETS`
// opt-in switch is enabled. The CDN-first default in functions.php is
// unaffected by the presence of this build pipeline.
//
// Run: `npm run build` (or `npm run dev` for incremental builds). All
// outputs land in `dist/` (gitignored).

import { defineConfig } from 'vite';
import { resolve } from 'node:path';
import { readFileSync, writeFileSync, mkdirSync } from 'node:fs';
import { dirname } from 'node:path';

// Map from Vite's source-relative manifest keys to the friendly logical
// aliases the rest of the project uses (`contracts/manifest.schema.json`).
const KEY_ALIAS = {
  'assets/js/app.js': 'app',
  'style.css': 'style',
};

// After Vite writes its stock manifest at dist/.vite/manifest.json,
// rewrite it to dist/manifest.json with logical-name keys.
function friendlyManifestPlugin() {
  return {
    name: 'vue-blocks-friendly-manifest',
    apply: 'build',
    closeBundle() {
      const src = resolve(__dirname, 'dist/.vite/manifest.json');
      const dst = resolve(__dirname, 'dist/manifest.json');
      let raw;
      try {
        raw = JSON.parse(readFileSync(src, 'utf8'));
      } catch (err) {
        // No source manifest yet (e.g., watch mode pre-first-build) —
        // nothing to rewrite.
        return;
      }
      const out = {};
      for (const [srcKey, entry] of Object.entries(raw)) {
        const alias = KEY_ALIAS[srcKey];
        if (!alias) continue;
        out[alias] = {
          file: entry.file,
          isEntry: true,
        };
        if (entry.imports && entry.imports.length) {
          out[alias].imports = entry.imports;
        }
        if (entry.css && entry.css.length) {
          out[alias].css = entry.css;
        }
      }
      mkdirSync(dirname(dst), { recursive: true });
      writeFileSync(dst, JSON.stringify(out, null, 2) + '\n');
    },
  };
}

export default defineConfig({
  root: '.',
  base: './',
  plugins: [friendlyManifestPlugin()],
  build: {
    outDir: 'dist',
    emptyOutDir: true,
    sourcemap: true,
    manifest: true,
    rollupOptions: {
      input: {
        app: resolve(__dirname, 'theme/assets/js/app.js'),
        style: resolve(__dirname, 'theme/style.css'),
      },
      output: {
        entryFileNames: 'assets/[name].[hash].js',
        chunkFileNames: 'assets/[name].[hash].js',
        assetFileNames: 'assets/[name].[hash].[ext]',
      },
    },
  },
});
// Builds the theme assets into hide-atelier/assets and (with --zip) creates
// dist/hide-atelier.zip ready to upload in WordPress → Appearance → Themes.
import * as esbuild from 'esbuild';
import { mkdirSync, copyFileSync, rmSync, existsSync, readdirSync, statSync } from 'node:fs';
import { join } from 'node:path';
import AdmZip from 'adm-zip';

const THEME = 'hide-atelier';
const A = `${THEME}/assets`;
rmSync(`${A}/js`, { recursive: true, force: true });
rmSync(`${A}/css`, { recursive: true, force: true });
mkdirSync(`${A}/fonts`, { recursive: true });

const fonts = {
  fraunces: ['fraunces-latin-500-normal', 'fraunces-latin-600-normal', 'fraunces-latin-500-italic'],
  inter: ['inter-latin-400-normal', 'inter-latin-500-normal', 'inter-latin-600-normal'],
};
for (const [pkg, files] of Object.entries(fonts)) {
  for (const f of files) copyFileSync(`node_modules/@fontsource/${pkg}/files/${f}.woff2`, `${A}/fonts/${f}.woff2`);
}

const common = { bundle: true, minify: true, target: ['es2019', 'chrome90', 'safari14'], legalComments: 'none', logLevel: 'info' };

await esbuild.build({ ...common, entryPoints: ['src/site.js'], outfile: `${A}/js/site.js`, format: 'iife' });
await esbuild.build({
  ...common,
  entryPoints: ['src/home.js'],
  outdir: `${A}/js`,
  format: 'esm',
  splitting: true,
  entryNames: '[name]',
  chunkNames: 'chunks/[name]-[hash]',
});
await esbuild.build({ ...common, entryPoints: ['src/main.css'], outfile: `${A}/css/main.css`, external: ['*.woff2'] });

if (process.argv.includes('--zip')) {
  mkdirSync('dist', { recursive: true });
  const zip = new AdmZip();
  const add = (dir) => {
    for (const name of readdirSync(dir)) {
      const p = join(dir, name);
      if (name === '.DS_Store') continue;
      if (statSync(p).isDirectory()) add(p);
      else zip.addLocalFile(p, dir);
    }
  };
  add(THEME);
  const out = `dist/${THEME}.zip`;
  if (existsSync(out)) rmSync(out);
  zip.writeZip(out);
  console.log('Created', out);
}

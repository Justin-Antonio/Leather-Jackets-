// Prepares the demo product images bundled with the theme:
// originals, a close-up detail crop for each, and a size chart graphic.
import sharp from 'sharp';
import { mkdirSync, copyFileSync, chmodSync } from 'node:fs';

const SRC = '../public/images';
const OUT = 'hide-atelier/assets/images/products';
mkdirSync(OUT, { recursive: true });

const crops = {
  'cognac-asymmetric-biker.jpg': [0.42, 0.08, 0.52, 0.5],
  'midnight-hooded-jacket.webp': [0.18, 0.16, 0.64, 0.5],
  'onyx-hooded-moto.webp': [0.18, 0.12, 0.64, 0.48],
  'camel-quilted-cafe-racer.jpg': [0.44, 0.02, 0.52, 0.5],
  'tobacco-cafe-racer.webp': [0.22, 0.04, 0.56, 0.46],
};

for (const [file, [x, y, w, h]] of Object.entries(crops)) {
  copyFileSync(`${SRC}/${file}`, `${OUT}/${file}`);
  chmodSync(`${OUT}/${file}`, 0o644);
  const img = sharp(`${SRC}/${file}`);
  const { width, height } = await img.metadata();
  const box = { left: Math.round(x * width), top: Math.round(y * height), width: Math.round(w * width), height: Math.round(h * height) };
  const name = file.replace(/\.(jpg|webp)$/, '-detail.jpg');
  await sharp(`${SRC}/${file}`)
    .extract(box)
    .resize({ width: 900, kernel: 'lanczos3' })
    .sharpen({ sigma: 0.8 })
    .flatten({ background: '#ffffff' })
    .jpeg({ quality: 84, mozjpeg: true })
    .toFile(`${OUT}/${name}`);
  console.log('detail', name);
}

// Size chart graphic (used as the last gallery image, like the reference design).
const rows = [
  ['S', '36–38', '30–32', '17', '24.5', '25'],
  ['M', '38–40', '32–34', '17.75', '25', '26'],
  ['L', '40–42', '34–36', '18.5', '25.5', '27'],
  ['XL', '42–44', '36–38', '19.25', '26', '28'],
  ['XXL', '44–46', '38–40', '20', '26.5', '29'],
  ['XXXL', '46–48', '40–42', '20.75', '27', '30'],
];
const head = ['SIZE', 'CHEST', 'WAIST', 'SHOULDER', 'SLEEVE', 'LENGTH'];
const W = 1000, H = 1250, x0 = 70, colW = (W - 2 * x0) / head.length, y0 = 330, rowH = 110;
let svg = `<svg xmlns="http://www.w3.org/2000/svg" width="${W}" height="${H}" viewBox="0 0 ${W} ${H}">
<rect width="100%" height="100%" fill="#f4ece1"/>
<text x="${W / 2}" y="150" text-anchor="middle" font-family="Georgia, serif" font-size="72" font-weight="bold" fill="#1d130d" letter-spacing="6">SIZE CHART</text>
<text x="${W / 2}" y="215" text-anchor="middle" font-family="Helvetica, Arial, sans-serif" font-size="28" fill="#8f4a1e" letter-spacing="5">MEN'S LEATHER JACKETS</text>
<rect x="${x0}" y="${y0 - 80}" width="${W - 2 * x0}" height="80" rx="14" fill="#1d130d"/>`;
head.forEach((t, i) => {
  svg += `<text x="${x0 + colW * i + colW / 2}" y="${y0 - 28}" text-anchor="middle" font-family="Helvetica, Arial, sans-serif" font-size="24" font-weight="bold" fill="#f4ece1" letter-spacing="2">${t}</text>`;
});
rows.forEach((r, ri) => {
  const y = y0 + ri * rowH;
  svg += `<rect x="${x0}" y="${y}" width="${W - 2 * x0}" height="${rowH}" fill="${ri % 2 ? '#efe4d5' : '#fbf7f1'}"/>`;
  r.forEach((t, i) => {
    svg += `<text x="${x0 + colW * i + colW / 2}" y="${y + rowH / 2 + 12}" text-anchor="middle" font-family="${i ? 'Helvetica, Arial, sans-serif' : 'Georgia, serif'}" font-size="${i ? 32 : 36}" font-weight="${i ? 'normal' : 'bold'}" fill="${i ? '#3b2a1f' : '#8f4a1e'}">${t}</text>`;
  });
});
svg += `<text x="${W / 2}" y="${y0 + rows.length * rowH + 90}" text-anchor="middle" font-family="Helvetica, Arial, sans-serif" font-size="26" fill="#74624f">All measurements are body measurements in inches</text></svg>`;
await sharp(Buffer.from(svg)).png({ compressionLevel: 9 }).toFile(`${OUT}/size-chart.png`);
console.log('size-chart.png');

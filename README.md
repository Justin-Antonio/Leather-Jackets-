# Hide & Atelier — 3D Leather Jacket Store

A premium e-commerce landing site for leather jackets with a real-time 3D hero built in **Three.js** and scroll animations with **GSAP**.

## Highlights

- **3D "wearing" intro**: a tailor's dress form rises on a pedestal and the jacket panels (back, fronts, waistband, sleeves, collar) fly in and wrap around it.
- **Live stitching**: every seam is drawn thread-by-thread with a glowing needle head, then the centre zip closes from hem to chest.
- **Procedural leather**: pebble-grain normal map, roughness, clear-coat sheen, plaid lining, nickel YKK-style hardware. All generated in code, so there are no heavy model files.
- **Colour switcher**: Cognac, Midnight Black, Camel and Tobacco, animated in real time.
- **Drag to rotate** with inertia, mouse parallax, floating dust particles.
- **Anatomy scroll section**: the jacket explodes into its panels with hotspot labels (hide, stitching, hardware, lining) as you scroll.
- **Shop**: filters, 3D tilt cards, quick view with zoom, sizes, cart drawer (saved in localStorage), wishlist, free-shipping progress bar, checkout form with validation (Cash on Delivery / Bank Transfer).
- Process timeline, animated counters, reviews slider, newsletter and contact forms, size guide, fully responsive.

## Run locally

```bash
npm install
npm run dev      # development server
npm run build    # production build in /dist
npm run preview  # preview the production build
```

The `dist/` folder is static. Upload it to Netlify, Vercel, GitHub Pages, cPanel or any other host.

## Customise

| What | Where |
| --- | --- |
| Products, prices, currency, reviews | `src/products.js` |
| Product images | `public/images/` |
| 3D leather colours / thread colours | `LEATHER_COLORS` in `src/scene.js` |
| Brand name, texts, sections | `index.html` |
| Theme colours and fonts | `:root` in `src/style.css` |

> Note: orders, messages and newsletter sign-ups are stored in the browser (localStorage) for the demo.
> Connect the checkout and contact forms to your backend, WhatsApp or an email service for production.

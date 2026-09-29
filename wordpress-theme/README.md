# Hide Atelier — WordPress + WooCommerce theme

Ready-to-upload theme: `dist/hide-atelier.zip` (build it with `npm install && npm run zip`).

## Install (Hostinger or any WordPress host)

1. **Install WordPress** (Hostinger hPanel → Websites → Auto Installer → WordPress).
2. **Install WooCommerce**: WordPress admin → Plugins → Add New → search "WooCommerce" → Install → Activate.
   You can skip WooCommerce's setup wizard.
3. **Upload the theme**: Appearance → Themes → Add New → Upload Theme → choose `hide-atelier.zip` → Install → Activate.
4. **One-click setup**: Appearance → **Hide Atelier Setup** → keep both boxes ticked → **Run setup**.
   This creates:
   - Pages: Home, Our Craft, Contact, Size Guide, FAQ
   - Policies: Privacy, Shipping, Refund & Return, Terms & Conditions
   - Header and footer menus
   - 5 demo jackets with photos, gallery and sizes S–XXXL (edit or delete them later)
   - Store currency PKR (Rs.), shipping zones (Pakistan + worldwide), Cash on Delivery and bank transfer
5. **Your details**: Appearance → Customize → **Hide Atelier** → store name, email, phone, WhatsApp number,
   address, delivery days, free-delivery amount, hero text, currencies. Policy pages update automatically.
6. Recommended: install **LiteSpeed Cache** (already on Hostinger) for page caching.

## Adding your own jackets

WooCommerce → Products → Add New:

- **Product image** = main photo (plain white/light background works best for the 3D hero).
- **Product gallery** = extra photos (shown as thumbnails on the product page).
- Product type **Variable product** → Attributes → add `Size` with `S | M | L | XL | XXL | XXXL`, tick
  "Used for variations" → Variations → "Generate variations" → set the price.
- **Jacket details** box (General tab): material line, tagline, badge, feature checklist, delivery note.
- Tick **Featured** (the star in the products list) to show the jacket in the 3D hero.

## Features

- **3D hero with the real product photo**: background removed in the browser, depth map for volume,
  leather sheen that follows the mouse, drag to turn, stitching reveal animation, product switcher.
- **Scroll detail tour**: zooms into leather, seams, hardware and collar with labels.
- **Product page** like the reference: vertical thumbnails, in-stock badge, material line, tagline, rating,
  big price, feature checklist, size buttons with chest sizes, quantity, add to cart, trust badges,
  delivery note, WhatsApp link, size chart tab, sticky add-to-cart bar on mobile.
- **Automatic currency**: Pakistan → PKR, USA → USD, UK → GBP, UAE → AED, EU → EUR, etc.
  Uses Cloudflare/hosting country headers or WooCommerce geolocation, and the browser time zone as a fallback.
  Live exchange rates (every 12 h, free API) with manual override. Cart, checkout and orders are in the
  visitor's currency. Cash on Delivery only for PKR. Header currency switcher.
- **Speed**: no jQuery on non-shop pages, WooCommerce CSS/JS only on shop pages, self-hosted fonts,
  3D code (≈130 KB gzipped) loaded only on the home page *after* the page has painted, poster image for
  instant first view, emoji/embed scripts removed.
- Mobile-first layout, reduced-motion support, accessible markup.

## Developing

```bash
npm install
npm run images   # re-create demo images + size chart (optional)
npm run build    # compile src/ → hide-atelier/assets
npm run zip      # build + dist/hide-atelier.zip
```

Source: `src/site.js` (site-wide), `src/home.js` + `src/hero3d.js` (home page 3D), `src/main.css`.

> The policy texts are general templates. Please review them (ideally with a lawyer) for your business.

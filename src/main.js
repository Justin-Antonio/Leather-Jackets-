import './style.css';
import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { PRODUCTS, SIZES, REVIEWS, CURRENCY, FREE_SHIPPING_AT } from './products.js';
import { LEATHER_COLORS, createJacketScene } from './scene.js';

gsap.registerPlugin(ScrollTrigger);

const $ = (s, el = document) => el.querySelector(s);
const $$ = (s, el = document) => [...el.querySelectorAll(s)];
const money = (n) => `${CURRENCY}${n.toLocaleString('en-US')}`;
const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
const isTouch = window.matchMedia('(hover: none)').matches;

const store = {
  get(k, d) {
    try { return JSON.parse(localStorage.getItem(k)) ?? d; } catch { return d; }
  },
  set(k, v) {
    try { localStorage.setItem(k, JSON.stringify(v)); } catch { /* storage unavailable */ }
  },
};

$('#year').textContent = new Date().getFullYear();

/* ============================== 3D SCENE ============================== */
let jacket = null;
const hotspots = $$('.hotspot');
const hotspotWrap = $('#hotspots');

function initScene() {
  const canvas = $('#webgl');
  try {
    jacket = createJacketScene(canvas, {
      onFrame: ({ anchors, explode }) => {
        hotspotWrap.style.opacity = Math.min(1, explode * 1.6);
        hotspots.forEach((h) => {
          const a = anchors[h.dataset.key];
          if (!a) return;
          h.style.transform = `translate3d(${a.x.toFixed(1)}px, ${a.y.toFixed(1)}px, 0)`;
          h.classList.toggle('left', window.innerWidth < 700 && a.x > window.innerWidth * 0.5);
        });
      },
    });
  } catch (err) {
    console.warn('WebGL unavailable, using image fallback', err);
    document.body.classList.add('no-webgl');
  }
}

/* ============================== LOADER ============================== */
const loaderTexts = ['Tanning the hide…', 'Cutting the pattern…', 'Threading the needle…', 'Polishing the zips…'];
function runLoader() {
  return new Promise((resolve) => {
    const bar = $('#loaderBar');
    const txt = $('#loaderTxt');
    const p = { v: 0 };
    let step = 0;
    gsap.to(p, {
      v: 100,
      duration: reduceMotion ? 0.3 : 1.8,
      ease: 'power2.inOut',
      onUpdate: () => {
        bar.style.width = p.v + '%';
        const s = Math.min(loaderTexts.length - 1, Math.floor(p.v / 25));
        if (s !== step) { step = s; txt.textContent = loaderTexts[s]; }
      },
      onComplete: resolve,
    });
  });
}

function heroIntro() {
  const tl = gsap.timeline();
  tl.to('#loader', { yPercent: -100, duration: 1, ease: 'expo.inOut' })
    .set('#loader', { display: 'none' })
    .from('.hero__title .line > span', { yPercent: 110, duration: 1.1, stagger: 0.12, ease: 'expo.out' }, '-=0.45')
    .from('.reveal-hero', { y: 30, opacity: 0, duration: 0.9, stagger: 0.1, ease: 'power3.out' }, '-=0.8')
    .from('.nav', { y: -30, opacity: 0, duration: 0.8, ease: 'power3.out' }, '-=1');
  return tl;
}

/* ============================== HERO CONTROLS ============================== */
function setJacketColor(key) {
  $$('.swatch').forEach((s) => s.classList.toggle('active', s.dataset.color === key));
  $('#swatchName').textContent = LEATHER_COLORS[key]?.label ?? key;
  jacket?.setColor(key);
}
$$('.swatch').forEach((s) => s.addEventListener('click', () => setJacketColor(s.dataset.color)));
$('#replayBtn').addEventListener('click', () => {
  window.scrollTo({ top: 0, behavior: 'smooth' });
  jacket?.playIntro();
});

/* ============================== SCROLL SCENES ============================== */
function initScroll() {
  ScrollTrigger.create({
    trigger: '#hero',
    start: 'top top',
    end: 'bottom top',
    scrub: true,
    onUpdate: (st) => jacket?.setHeroProgress(st.progress),
  });
  gsap.to('.hero__content, .hero__hint, .scroll-cue', {
    opacity: 0, y: -80, ease: 'none',
    scrollTrigger: { trigger: '#hero', start: 'top top', end: '60% top', scrub: true },
  });

  const steps = $$('#anatSteps li');
  ScrollTrigger.create({
    trigger: '#anatomy',
    start: 'top top',
    end: '+=220%',
    pin: true,
    scrub: true,
    onUpdate: (st) => {
      jacket?.setAnatomyProgress(st.progress);
      $('#anatBar').style.width = st.progress * 100 + '%';
      const idx = Math.min(steps.length - 1, Math.floor(st.progress * steps.length * 0.999));
      steps.forEach((s, i) => s.classList.toggle('active', i === idx));
      hotspots.forEach((h) => {
        const map = { leather: 0, stitch: 1, collar: 1, zipper: 2, lining: 3 };
        h.classList.toggle('focus', map[h.dataset.key] === idx);
      });
    },
  });
  gsap.from('.anatomy__panel', {
    x: -60, opacity: 0, ease: 'power2.out',
    scrollTrigger: { trigger: '#anatomy', start: 'top 80%', end: 'top top', scrub: true },
  });

  // Stop rendering the 3D scene once it is fully covered by the page
  ScrollTrigger.create({
    trigger: '.marquee',
    start: 'top top',
    onEnter: () => { jacket?.setActive(false); $('#webgl').style.visibility = 'hidden'; hotspotWrap.style.visibility = 'hidden'; },
    onLeaveBack: () => { jacket?.setActive(true); $('#webgl').style.visibility = ''; hotspotWrap.style.visibility = ''; },
  });

  // Generic reveals
  ScrollTrigger.batch('.reveal', {
    start: 'top 88%',
    onEnter: (els) => gsap.to(els, { opacity: 1, y: 0, duration: 1, stagger: 0.08, ease: 'power3.out', overwrite: true }),
  });

  // Process thread draws with scroll
  const reveal = $('#threadReveal');
  const len = reveal.getTotalLength();
  reveal.style.strokeDasharray = len;
  reveal.style.strokeDashoffset = len;
  gsap.to(reveal, {
    strokeDashoffset: 0, ease: 'none',
    scrollTrigger: { trigger: '.process__wrap', start: 'top 75%', end: 'bottom 45%', scrub: true },
  });

  // Counters
  $$('[data-count]').forEach((el) => {
    const target = parseFloat(el.dataset.count);
    const dec = parseInt(el.dataset.decimals || '0', 10);
    const o = { v: 0 };
    ScrollTrigger.create({
      trigger: el, start: 'top 90%', once: true,
      onEnter: () => gsap.to(o, {
        v: target, duration: 2, ease: 'power2.out',
        onUpdate: () => (el.textContent = dec ? o.v.toFixed(dec) : Math.round(o.v).toLocaleString('en-US') + (target >= 1000 ? '+' : '')),
      }),
    });
  });

  // Banner image parallax
  gsap.fromTo('.banner__img img', { yPercent: -8 }, {
    yPercent: 8, ease: 'none',
    scrollTrigger: { trigger: '.banner', start: 'top bottom', end: 'bottom top', scrub: true },
  });

  // Nav background
  ScrollTrigger.create({
    start: 80,
    onUpdate: (st) => $('#nav').classList.toggle('scrolled', st.scroll() > 80),
  });
}

/* ============================== PRODUCTS ============================== */
const grid = $('#productGrid');
function renderProducts(filter = 'all') {
  const list = PRODUCTS.filter((p) => filter === 'all' || p.category === filter);
  grid.innerHTML = list
    .map(
      (p) => `
    <article class="card" data-id="${p.id}">
      <div class="card__media">
        ${p.badge ? `<span class="badge">${p.badge}</span>` : ''}
        <button class="card__wish ${wish.includes(p.id) ? 'on' : ''}" data-wish="${p.id}" aria-label="Add to wishlist">
          <svg viewBox="0 0 24 24"><path d="M12 21s-7.5-4.6-9.6-9.2C.9 8.4 3 4.5 6.7 4.5c2.1 0 3.6 1.2 5.3 3 1.7-1.8 3.2-3 5.3-3 3.7 0 5.8 3.9 4.3 7.3C19.5 16.4 12 21 12 21z"/></svg>
        </button>
        <img src="${p.image}" alt="${p.name}" loading="lazy" />
        <div class="card__glare"></div>
        <div class="card__actions">
          <button class="btn btn--dark btn--sm" data-quick="${p.id}">Quick View</button>
          <button class="btn btn--gold btn--sm" data-add="${p.id}">Add to Cart</button>
        </div>
      </div>
      <div class="card__body">
        <div>
          <p class="card__cat">${p.categoryLabel}</p>
          <h3>${p.name}</h3>
          <div class="card__rating">★★★★★ <span>(${p.reviews})</span></div>
        </div>
        <div class="card__price">${p.oldPrice ? `<s>${money(p.oldPrice)}</s>` : ''}<b>${money(p.price)}</b></div>
      </div>
      <button class="card__3d" data-view3d="${p.color3d}"><span class="cube"></span>See it in 3D</button>
    </article>`,
    )
    .join('');
  gsap.fromTo('.card', { y: 50, opacity: 0 }, { y: 0, opacity: 1, duration: 0.8, stagger: 0.08, ease: 'power3.out' });
  if (!isTouch) initTilt();
}

function initTilt() {
  $$('.card__media').forEach((m) => {
    const glare = $('.card__glare', m);
    m.addEventListener('mousemove', (e) => {
      const r = m.getBoundingClientRect();
      const x = (e.clientX - r.left) / r.width - 0.5;
      const y = (e.clientY - r.top) / r.height - 0.5;
      gsap.to(m, { rotateY: x * 14, rotateX: -y * 14, duration: 0.5, ease: 'power2.out' });
      gsap.to($('img', m), { x: x * 16, y: y * 16, scale: 1.06, duration: 0.6, ease: 'power2.out' });
      glare.style.background = `radial-gradient(circle at ${(x + 0.5) * 100}% ${(y + 0.5) * 100}%, rgba(255,255,255,0.35), transparent 55%)`;
    });
    m.addEventListener('mouseleave', () => {
      gsap.to(m, { rotateY: 0, rotateX: 0, duration: 0.8, ease: 'elastic.out(1,0.5)' });
      gsap.to($('img', m), { x: 0, y: 0, scale: 1, duration: 0.8, ease: 'power3.out' });
      glare.style.background = 'transparent';
    });
  });
}

$('#filters').addEventListener('click', (e) => {
  const b = e.target.closest('button');
  if (!b) return;
  $$('#filters button').forEach((x) => x.classList.toggle('active', x === b));
  renderProducts(b.dataset.filter);
});

grid.addEventListener('click', (e) => {
  const t = e.target.closest('button');
  if (!t) return;
  if (t.dataset.quick) openQuick(t.dataset.quick);
  if (t.dataset.add) addToCart(t.dataset.add, 'M', 1, t);
  if (t.dataset.wish) toggleWish(t.dataset.wish);
  if (t.dataset.view3d) viewIn3D(t.dataset.view3d);
});

function viewIn3D(color) {
  closeAll();
  window.scrollTo({ top: 0, behavior: reduceMotion ? 'auto' : 'smooth' });
  setTimeout(() => {
    setJacketColor(color);
    jacket?.spin();
  }, 500);
}

/* ============================== QUICK VIEW ============================== */
let qv = { id: null, size: 'M', qty: 1 };
function openQuick(id) {
  const p = PRODUCTS.find((x) => x.id === id);
  qv = { id, size: 'M', qty: 1 };
  $('#qvImg').src = p.image;
  $('#qvImg').alt = p.name;
  $('#qvName').textContent = p.name;
  $('#qvCat').textContent = p.categoryLabel;
  $('#qvReviews').textContent = `${p.reviews} reviews`;
  $('#qvPrice').innerHTML = `${p.oldPrice ? `<s>${money(p.oldPrice)}</s>` : ''}${money(p.price)}`;
  $('#qvDesc').textContent = p.desc;
  $('#qvQty').textContent = 1;
  $('#qvSizes').innerHTML = SIZES.map((s) => `<button class="${s === 'M' ? 'active' : ''}" data-size="${s}">${s}</button>`).join('');
  openModal('quickModal');
}
$('#qvSizes').addEventListener('click', (e) => {
  const b = e.target.closest('button');
  if (!b) return;
  qv.size = b.dataset.size;
  $$('#qvSizes button').forEach((x) => x.classList.toggle('active', x === b));
});
$$('#quickModal .qty button').forEach((b) =>
  b.addEventListener('click', () => {
    qv.qty = Math.max(1, Math.min(10, qv.qty + parseInt(b.dataset.q, 10)));
    $('#qvQty').textContent = qv.qty;
  }),
);
$('#qvAdd').addEventListener('click', (e) => {
  addToCart(qv.id, qv.size, qv.qty, e.currentTarget);
  closeModal('quickModal');
});
$('#qv3d').addEventListener('click', () => viewIn3D(PRODUCTS.find((p) => p.id === qv.id).color3d));

// zoom lens
const qvWrap = $('#qvImgWrap');
qvWrap.addEventListener('mousemove', (e) => {
  const r = qvWrap.getBoundingClientRect();
  const x = ((e.clientX - r.left) / r.width) * 100;
  const y = ((e.clientY - r.top) / r.height) * 100;
  $('#qvImg').style.transformOrigin = `${x}% ${y}%`;
  qvWrap.classList.add('zoom');
});
qvWrap.addEventListener('mouseleave', () => qvWrap.classList.remove('zoom'));

/* ============================== CART ============================== */
let cart = store.get('ha_cart', []);
let wish = store.get('ha_wish', []);

function saveCart() {
  store.set('ha_cart', cart);
  renderCart();
}
function addToCart(id, size, qty, fromEl) {
  const line = cart.find((l) => l.id === id && l.size === size);
  if (line) line.qty = Math.min(10, line.qty + qty);
  else cart.push({ id, size, qty });
  saveCart();
  flyToCart(fromEl, id);
  const p = PRODUCTS.find((x) => x.id === id);
  toast(`<b>${p.name}</b> (${size}) added to cart`);
  gsap.fromTo('#cartBtn', { scale: 1 }, { scale: 1.25, duration: 0.18, yoyo: true, repeat: 1 });
}
function flyToCart(fromEl, id) {
  if (!fromEl || reduceMotion) return;
  const p = PRODUCTS.find((x) => x.id === id);
  const from = fromEl.getBoundingClientRect();
  const to = $('#cartBtn').getBoundingClientRect();
  const img = document.createElement('img');
  img.src = p.image;
  img.className = 'fly-img';
  document.body.appendChild(img);
  gsap.set(img, { left: from.left + from.width / 2 - 40, top: from.top - 40 });
  gsap.to(img, {
    left: to.left - 20, top: to.top - 20, scale: 0.25, rotate: 20, opacity: 0.4,
    duration: 0.9, ease: 'power3.in', onComplete: () => img.remove(),
  });
}
function renderCart() {
  const count = cart.reduce((a, l) => a + l.qty, 0);
  $('#cartCount').textContent = count;
  $('#cartCount').classList.toggle('show', count > 0);
  const total = cart.reduce((a, l) => a + PRODUCTS.find((p) => p.id === l.id).price * l.qty, 0);
  $('#cartTotal').textContent = money(total);
  const left = FREE_SHIPPING_AT - total;
  $('#shipTxt').innerHTML = left > 0 ? `Add <b>${money(left)}</b> more for free shipping` : '🎉 You unlocked <b>free shipping</b>';
  $('#shipFill').style.width = Math.min(100, (total / FREE_SHIPPING_AT) * 100) + '%';
  $('#checkoutBtn').disabled = !cart.length;
  $('#cartItems').innerHTML = cart.length
    ? cart
        .map((l, i) => {
          const p = PRODUCTS.find((x) => x.id === l.id);
          return `<div class="cart-line">
            <img src="${p.image}" alt="${p.name}" />
            <div class="cart-line__info"><b>${p.name}</b><small>Size ${l.size}</small>
              <div class="qty qty--sm"><button data-dec="${i}">−</button><span>${l.qty}</span><button data-inc="${i}">+</button></div>
            </div>
            <div class="cart-line__end"><b>${money(p.price * l.qty)}</b><button class="link" data-rm="${i}">Remove</button></div>
          </div>`;
        })
        .join('')
    : `<div class="empty"><p>Your cart is empty.</p><a href="#collection" class="btn btn--ghost btn--sm" data-close>Browse jackets</a></div>`;
}
$('#cartItems').addEventListener('click', (e) => {
  const b = e.target.closest('button, a');
  if (!b) return;
  if (b.dataset.inc) cart[b.dataset.inc].qty = Math.min(10, cart[b.dataset.inc].qty + 1);
  else if (b.dataset.dec) {
    const l = cart[b.dataset.dec];
    l.qty -= 1;
    if (l.qty < 1) cart.splice(b.dataset.dec, 1);
  } else if (b.dataset.rm) cart.splice(b.dataset.rm, 1);
  else return;
  saveCart();
});

/* ============================== WISHLIST ============================== */
function toggleWish(id) {
  wish = wish.includes(id) ? wish.filter((w) => w !== id) : [...wish, id];
  store.set('ha_wish', wish);
  renderWish();
  $$(`[data-wish="${id}"]`).forEach((b) => b.classList.toggle('on', wish.includes(id)));
  const p = PRODUCTS.find((x) => x.id === id);
  toast(wish.includes(id) ? `♥ Saved <b>${p.name}</b>` : `Removed <b>${p.name}</b> from wishlist`);
}
function renderWish() {
  $('#wishCount').textContent = wish.length;
  $('#wishCount').classList.toggle('show', wish.length > 0);
  $('#wishItems').innerHTML = wish.length
    ? wish
        .map((id) => {
          const p = PRODUCTS.find((x) => x.id === id);
          return `<div class="cart-line"><img src="${p.image}" alt="${p.name}" />
            <div class="cart-line__info"><b>${p.name}</b><small>${money(p.price)}</small></div>
            <div class="cart-line__end"><button class="btn btn--gold btn--sm" data-wadd="${id}">Add</button><button class="link" data-wrm="${id}">Remove</button></div></div>`;
        })
        .join('')
    : `<div class="empty"><p>No saved jackets yet.</p></div>`;
}
$('#wishItems').addEventListener('click', (e) => {
  const b = e.target.closest('button');
  if (!b) return;
  if (b.dataset.wadd) addToCart(b.dataset.wadd, 'M', 1, b);
  if (b.dataset.wrm) toggleWish(b.dataset.wrm);
});

/* ============================== DRAWERS & MODALS ============================== */
const overlay = $('#overlay');
function openDrawer(id) {
  closeAll();
  $('#' + id).classList.add('open');
  overlay.classList.add('show');
  document.body.classList.add('locked');
}
function openModal(id) {
  $$('.drawer.open').forEach((d) => d.classList.remove('open'));
  $$('.modal.open').forEach((d) => d.classList.remove('open'));
  $('#' + id).classList.add('open');
  overlay.classList.add('show');
  document.body.classList.add('locked');
}
function closeModal(id) {
  $('#' + id).classList.remove('open');
  if (!$('.modal.open, .drawer.open')) closeAll();
}
function closeAll() {
  $$('.drawer.open, .modal.open').forEach((d) => d.classList.remove('open'));
  overlay.classList.remove('show');
  document.body.classList.remove('locked');
  $('#navLinks').classList.remove('open');
  $('#burger').classList.remove('open');
}
$('#cartBtn').addEventListener('click', () => openDrawer('cartDrawer'));
$('#wishBtn').addEventListener('click', () => openDrawer('wishDrawer'));
overlay.addEventListener('click', closeAll);
document.addEventListener('click', (e) => {
  if (e.target.closest('[data-close]')) closeAll();
  const o = e.target.closest('[data-open]');
  if (o) { e.preventDefault(); openModal(o.dataset.open); }
});
$$('.modal').forEach((m) => m.addEventListener('click', (e) => e.target === m && closeAll()));
document.addEventListener('keydown', (e) => e.key === 'Escape' && closeAll());
$('#sizeGuideBtn').addEventListener('click', () => openModal('sizeModal'));

$('#burger').addEventListener('click', () => {
  const open = !$('#navLinks').classList.contains('open');
  $('#navLinks').classList.toggle('open', open);
  $('#burger').classList.toggle('open', open);
});
$$('#navLinks a').forEach((a) => a.addEventListener('click', () => {
  $('#navLinks').classList.remove('open');
  $('#burger').classList.remove('open');
}));

/* ============================== CHECKOUT ============================== */
const checkoutTemplate = $('#checkoutBody').innerHTML;
$('#checkoutBtn').addEventListener('click', () => {
  if (!cart.length) return;
  $('#checkoutBody').innerHTML = checkoutTemplate;
  const total = cart.reduce((a, l) => a + PRODUCTS.find((p) => p.id === l.id).price * l.qty, 0);
  const ship = total >= FREE_SHIPPING_AT ? 0 : 15;
  $('#coSummary').innerHTML =
    cart.map((l) => {
      const p = PRODUCTS.find((x) => x.id === l.id);
      return `<div><span>${l.qty} × ${p.name} (${l.size})</span><b>${money(p.price * l.qty)}</b></div>`;
    }).join('') +
    `<div><span>Shipping</span><b>${ship ? money(ship) : 'Free'}</b></div><div class="total"><span>Total</span><b>${money(total + ship)}</b></div>`;
  openModal('checkoutModal');
});

function validate(form) {
  let ok = true;
  $$('input, textarea', form).forEach((i) => {
    const valid = i.checkValidity();
    i.classList.toggle('invalid', !valid);
    if (!valid) ok = false;
  });
  return ok;
}
const escapeHtml = (s) => s.replace(/[&<>"']/g, (c) => `&#${c.charCodeAt(0)};`);
$('#checkoutModal').addEventListener('submit', (e) => {
  if (e.target.id !== 'checkoutForm') return;
  e.preventDefault();
  const f = e.target;
  if (!validate(f)) {
    $('#coMsg').textContent = 'Please fill in all fields correctly.';
    return;
  }
  const data = Object.fromEntries(new FormData(f));
  const orderNo = 'HA-' + Math.random().toString(36).slice(2, 8).toUpperCase();
  const orders = store.get('ha_orders', []);
  orders.push({ orderNo, ...data, cart, date: new Date().toISOString() });
  store.set('ha_orders', orders);
  cart = [];
  saveCart();
  $('#checkoutBody').innerHTML = `<div class="success">
      <svg viewBox="0 0 52 52"><circle cx="26" cy="26" r="24"/><path d="M15 27l7 7 15-15"/></svg>
      <h3>Thank you, ${escapeHtml(data.name.split(' ')[0])}!</h3>
      <p>Your order <b>${orderNo}</b> is confirmed. Our tailors are already threading the needle — we'll email updates to you.</p>
      <button class="btn btn--gold" data-close>Continue shopping</button></div>`;
  gsap.from('.success > *', { y: 20, opacity: 0, stagger: 0.1, duration: 0.6 });
});

/* ============================== FORMS ============================== */
$('#newsForm').addEventListener('submit', (e) => {
  e.preventDefault();
  const f = e.currentTarget;
  const msg = $('#newsMsg');
  if (!validate(f)) { msg.textContent = 'Please enter a valid email.'; msg.className = 'form-msg err'; return; }
  const subs = store.get('ha_subscribers', []);
  subs.push(f.email.value);
  store.set('ha_subscribers', subs);
  msg.textContent = 'Welcome! Your 10% code: ATELIER10';
  msg.className = 'form-msg ok';
  f.reset();
});
$('#contactForm').addEventListener('submit', (e) => {
  e.preventDefault();
  const f = e.currentTarget;
  const msg = $('#contactMsg');
  if (!validate(f)) { msg.textContent = 'Please complete all fields.'; msg.className = 'form-msg err'; return; }
  const msgs = store.get('ha_messages', []);
  msgs.push({ ...Object.fromEntries(new FormData(f)), date: new Date().toISOString() });
  store.set('ha_messages', msgs);
  msg.textContent = 'Thanks! A tailor will reply within 24 hours.';
  msg.className = 'form-msg ok';
  f.reset();
});
document.addEventListener('input', (e) => e.target.classList?.remove('invalid'));

/* ============================== REVIEWS SLIDER ============================== */
function initSlider() {
  const track = $('#sliderTrack');
  track.innerHTML = REVIEWS.map(
    (r) => `<figure class="review"><div class="review__stars">★★★★★</div><blockquote>“${r.text}”</blockquote>
      <figcaption><span class="avatar">${r.name[0]}</span><div><b>${r.name}</b><small>${r.city} · ${r.product}</small></div></figcaption></figure>`,
  ).join('');
  const dots = $('#sliderDots');
  dots.innerHTML = REVIEWS.map((_, i) => `<button aria-label="Review ${i + 1}" data-i="${i}"></button>`).join('');
  let i = 0;
  const go = (n) => {
    i = (n + REVIEWS.length) % REVIEWS.length;
    track.style.transform = `translateX(-${i * 100}%)`;
    $$('button', dots).forEach((d, k) => d.classList.toggle('active', k === i));
  };
  dots.addEventListener('click', (e) => e.target.dataset.i && (go(+e.target.dataset.i), reset()));
  let timer;
  const reset = () => { clearInterval(timer); timer = setInterval(() => go(i + 1), 5500); };
  let sx = null;
  track.addEventListener('pointerdown', (e) => (sx = e.clientX));
  track.addEventListener('pointerup', (e) => {
    if (sx !== null && Math.abs(e.clientX - sx) > 40) { go(i + (e.clientX < sx ? 1 : -1)); reset(); }
    sx = null;
  });
  go(0);
  reset();
}

/* ============================== CURSOR & MAGNETIC ============================== */
function initCursor() {
  if (isTouch) return;
  const c = $('#cursor');
  document.body.classList.add('has-cursor');
  const xTo = gsap.quickTo(c, 'x', { duration: 0.25, ease: 'power3' });
  const yTo = gsap.quickTo(c, 'y', { duration: 0.25, ease: 'power3' });
  window.addEventListener('pointermove', (e) => {
    xTo(e.clientX);
    yTo(e.clientY);
    const t = e.target;
    c.classList.toggle('hover', !!t.closest?.('a, button, .card__media, input, textarea'));
    c.classList.toggle('drag', t.id === 'webgl' || !!t.closest?.('.hero') && !t.closest('a, button'));
  });
  $$('.magnetic').forEach((m) => {
    m.addEventListener('mousemove', (e) => {
      const r = m.getBoundingClientRect();
      gsap.to(m, { x: (e.clientX - r.left - r.width / 2) * 0.25, y: (e.clientY - r.top - r.height / 2) * 0.35, duration: 0.4 });
    });
    m.addEventListener('mouseleave', () => gsap.to(m, { x: 0, y: 0, duration: 0.6, ease: 'elastic.out(1,0.4)' }));
  });
}

/* ============================== TOAST ============================== */
let toastTimer;
function toast(html) {
  const t = $('#toast');
  t.innerHTML = html;
  t.classList.add('show');
  clearTimeout(toastTimer);
  toastTimer = setTimeout(() => t.classList.remove('show'), 2600);
}

/* ============================== BOOT ============================== */
renderProducts();
renderCart();
renderWish();
initSlider();
initCursor();

(async () => {
  await Promise.all([initScene(), runLoader()]);
  initScroll();
  heroIntro();
  if (jacket) setTimeout(() => jacket.playIntro(), reduceMotion ? 0 : 500);
  ScrollTrigger.refresh();
})();

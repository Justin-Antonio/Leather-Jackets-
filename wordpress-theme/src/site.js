/**
 * Lightweight site-wide script (no dependencies): header, drawers, currency,
 * reveals, product gallery, size buttons, size guide, policy contents.
 */
(() => {
  const $ = (s, el = document) => el.querySelector(s);
  const $$ = (s, el = document) => [...el.querySelectorAll(s)];
  const HA = window.HA || {};
  const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* ---------------- currency ---------------- */
  const COOKIE = HA.cookie || 'ha_cur';
  const getCookie = (n) => document.cookie.split('; ').find((c) => c.startsWith(n + '='))?.split('=')[1];
  const setCookie = (n, v) => {
    document.cookie = `${n}=${v}; path=/; max-age=${60 * 60 * 24 * 180}; SameSite=Lax`;
  };
  const TZ = {
    'Asia/Karachi': 'PKR', 'Europe/London': 'GBP', 'Asia/Dubai': 'AED', 'Asia/Riyadh': 'SAR', 'Asia/Qatar': 'QAR',
    'Asia/Kuwait': 'KWD', 'Asia/Muscat': 'OMR', 'Asia/Bahrain': 'BHD', 'Asia/Kolkata': 'INR', 'Asia/Calcutta': 'INR',
    'Europe/Istanbul': 'TRY', 'Asia/Kuala_Lumpur': 'MYR', 'Asia/Singapore': 'SGD', 'Pacific/Auckland': 'NZD',
    'Europe/Zurich': 'CHF', 'Europe/Oslo': 'NOK', 'Europe/Stockholm': 'SEK', 'Europe/Copenhagen': 'DKK',
    'Asia/Tokyo': 'JPY', 'Asia/Shanghai': 'CNY', 'Africa/Johannesburg': 'ZAR',
  };
  const CA = ['Toronto', 'Vancouver', 'Edmonton', 'Winnipeg', 'Halifax', 'Regina', 'St_Johns', 'Montreal'];
  function currencyFromTimezone() {
    let tz = '';
    try { tz = Intl.DateTimeFormat().resolvedOptions().timeZone || ''; } catch { /* ignore */ }
    if (TZ[tz]) return TZ[tz];
    if (tz.startsWith('Australia/')) return 'AUD';
    if (tz.startsWith('America/')) return CA.some((c) => tz.endsWith(c)) ? 'CAD' : 'USD';
    if (tz.startsWith('Europe/')) return 'EUR';
    return tz ? 'USD' : '';
  }
  const list = HA.currencies || [];
  if (list.length > 1) {
    if (!getCookie(COOKIE)) {
      let want = currencyFromTimezone();
      if (!list.includes(want)) want = list.includes('USD') && want !== list[0] ? 'USD' : list[0];
      setCookie(COOKIE, want);
      // Reload once so prices render in the visitor's currency (never loops if cookies are blocked).
      if (want && want !== HA.currency && getCookie(COOKIE) === want) location.reload();
    }
    $$('.js-currency').forEach((sel) =>
      sel.addEventListener('change', () => {
        setCookie(COOKIE, sel.value);
        location.reload();
      }),
    );
  }

  /* ---------------- header ---------------- */
  const header = $('#siteHeader');
  const onScroll = () => header?.classList.toggle('scrolled', window.scrollY > 40);
  onScroll();
  window.addEventListener('scroll', onScroll, { passive: true });

  const burger = $('#burger');
  const nav = $('#mainNav');
  burger?.addEventListener('click', () => {
    const open = !nav.classList.contains('open');
    nav.classList.toggle('open', open);
    burger.classList.toggle('open', open);
    burger.setAttribute('aria-expanded', open ? 'true' : 'false');
    document.body.classList.toggle('locked', open);
  });

  /* ---------------- cart drawer ---------------- */
  const drawer = $('#cartDrawer');
  const overlay = $('#overlay');
  function openDrawer() {
    if (!drawer) return false;
    drawer.classList.add('open');
    drawer.setAttribute('aria-hidden', 'false');
    overlay.hidden = false;
    requestAnimationFrame(() => overlay.classList.add('show'));
    document.body.classList.add('locked');
    return true;
  }
  function closeDrawer() {
    if (!drawer) return;
    drawer.classList.remove('open');
    drawer.setAttribute('aria-hidden', 'true');
    overlay.classList.remove('show');
    setTimeout(() => (overlay.hidden = true), 300);
    document.body.classList.remove('locked');
  }
  $$('.js-cart-open').forEach((a) => a.addEventListener('click', (e) => { if (openDrawer()) e.preventDefault(); }));
  $$('.js-drawer-close').forEach((b) => b.addEventListener('click', closeDrawer));
  overlay?.addEventListener('click', closeDrawer);
  document.addEventListener('keydown', (e) => e.key === 'Escape' && closeDrawer());
  // After "Add to cart" on a product page, show the cart.
  if ($('.woocommerce-message .wc-forward') && document.body.classList.contains('single-product')) {
    setTimeout(openDrawer, 350);
  }
  if (window.jQuery) {
    window.jQuery(document.body).on('added_to_cart', () => setTimeout(openDrawer, 200));
  }

  /* ---------------- reveals & counters ---------------- */
  const io = 'IntersectionObserver' in window
    ? new IntersectionObserver((entries) => {
      entries.forEach((en) => {
        if (!en.isIntersecting) return;
        en.target.classList.add('in');
        io.unobserve(en.target);
        const num = en.target.querySelector('[data-count]');
        if (num) countUp(num);
      });
    }, { rootMargin: '0px 0px -10% 0px' })
    : null;
  $$('.reveal, .stat, .process__wrap').forEach((el, i) => {
    el.style.transitionDelay = `${(i % 4) * 70}ms`;
    if (io && !reduced) io.observe(el);
    else el.classList.add('in');
  });
  function countUp(el) {
    const target = parseFloat(el.dataset.count);
    const suffix = el.dataset.suffix || '';
    if (Number.isNaN(target) || reduced) return;
    const start = performance.now();
    const step = (now) => {
      const k = Math.min(1, (now - start) / 1600);
      const v = target * (1 - Math.pow(1 - k, 3));
      el.textContent = (target % 1 ? v.toFixed(1) : Math.round(v)) + suffix;
      if (k < 1) requestAnimationFrame(step);
    };
    requestAnimationFrame(step);
  }

  /* ---------------- home category chips ---------------- */
  const chips = $('#filters');
  chips?.addEventListener('click', (e) => {
    const b = e.target.closest('button');
    if (!b) return;
    $$('button', chips).forEach((x) => x.classList.toggle('active', x === b));
    const f = b.dataset.filter;
    $$('#homeGrid .card').forEach((c) => {
      c.hidden = !(f === 'all' || (c.dataset.cats || '').split(' ').includes(f));
    });
  });

  /* ---------------- reviews slider ---------------- */
  $$('[data-slider]').forEach((s) => {
    const track = $('.slider__track', s);
    const dots = $('.slider__dots', s);
    const n = track.children.length;
    if (n < 2) return;
    dots.innerHTML = Array.from({ length: n }, (_, i) => `<button aria-label="${i + 1}"></button>`).join('');
    let i = 0;
    let timer;
    const go = (k) => {
      i = (k + n) % n;
      track.style.transform = `translateX(-${i * 100}%)`;
      $$('button', dots).forEach((d, j) => d.classList.toggle('active', j === i));
    };
    const reset = () => { clearInterval(timer); timer = setInterval(() => go(i + 1), 6000); };
    dots.addEventListener('click', (e) => {
      const idx = [...dots.children].indexOf(e.target);
      if (idx >= 0) { go(idx); reset(); }
    });
    let sx = null;
    track.addEventListener('pointerdown', (e) => (sx = e.clientX));
    track.addEventListener('pointerup', (e) => {
      if (sx !== null && Math.abs(e.clientX - sx) > 40) { go(i + (e.clientX < sx ? 1 : -1)); reset(); }
      sx = null;
    });
    go(0);
    reset();
  });

  /* ---------------- product gallery ---------------- */
  $$('[data-gallery]').forEach((g) => {
    const track = $('.pgallery__track', g);
    const slides = $$('.pgallery__slide', g);
    const thumbs = $$('.pgallery__thumb', g);
    const dots = $$('.pgallery__dots span', g);
    const setActive = (i) => {
      thumbs.forEach((t, k) => { t.classList.toggle('active', k === i); t.setAttribute('aria-selected', k === i); });
      dots.forEach((d, k) => d.classList.toggle('active', k === i));
    };
    const go = (i) => {
      track.scrollTo({ left: slides[i].offsetLeft, behavior: reduced ? 'auto' : 'smooth' });
      setActive(i);
    };
    thumbs.forEach((t) => t.addEventListener('click', () => go(+t.dataset.index)));
    track.addEventListener('scroll', () => {
      const i = Math.round(track.scrollLeft / track.clientWidth);
      setActive(Math.max(0, Math.min(slides.length - 1, i)));
    }, { passive: true });
    // Hover zoom (desktop)
    if (window.matchMedia('(hover: hover)').matches) {
      slides.forEach((s) => {
        const img = $('img', s);
        if (!img) return;
        s.addEventListener('mousemove', (e) => {
          const r = s.getBoundingClientRect();
          img.style.transformOrigin = `${((e.clientX - r.left) / r.width) * 100}% ${((e.clientY - r.top) / r.height) * 100}%`;
          s.classList.add('zoom');
          if (s.dataset.zoom && img.dataset.full !== '1') {
            img.dataset.full = '1';
            img.srcset = '';
            img.src = s.dataset.zoom;
          }
        });
        s.addEventListener('mouseleave', () => s.classList.remove('zoom'));
      });
    }
  });

  /* ---------------- size buttons ---------------- */
  $$('.swatch-group').forEach((group) => {
    const select = group.parentElement.querySelector('.swatch-select select');
    if (!select) return;
    const label = document.createElement('span');
    label.className = 'swatch-current';
    const row = group.closest('tr');
    row?.querySelector('th.label, td.label')?.appendChild(label);
    const sync = () => {
      const available = new Set([...select.options].map((o) => o.value).filter(Boolean));
      $$('.swatch-btn', group).forEach((b) => {
        b.classList.toggle('active', b.dataset.value === select.value);
        b.disabled = !available.has(b.dataset.value);
      });
      const cur = $$('.swatch-btn', group).find((b) => b.dataset.value === select.value);
      label.textContent = cur ? cur.dataset.label : '';
    };
    group.addEventListener('click', (e) => {
      const b = e.target.closest('.swatch-btn');
      if (!b || b.disabled) return;
      const val = b.dataset.value === select.value ? '' : b.dataset.value;
      select.value = val;
      // WooCommerce listens with jQuery.
      if (window.jQuery) window.jQuery(select).trigger('change');
      else select.dispatchEvent(new Event('change', { bubbles: true }));
      sync();
    });
    select.addEventListener('change', sync);
    if (window.jQuery) window.jQuery(select.form).on('woocommerce_update_variation_values reset_data', () => setTimeout(sync));
    sync();
  });

  /* ---------------- quantity +/- ---------------- */
  $$('form.cart .quantity').forEach((q) => {
    const input = $('input.qty', q);
    if (!input || input.type === 'hidden' || q.querySelector('.qty-btn')) return;
    const mk = (d, t) => {
      const b = document.createElement('button');
      b.type = 'button';
      b.className = 'qty-btn';
      b.textContent = t;
      b.setAttribute('aria-label', d > 0 ? 'Increase' : 'Decrease');
      b.addEventListener('click', () => {
        const min = parseFloat(input.min) || 1;
        const max = parseFloat(input.max) || 99;
        input.value = Math.max(min, Math.min(max, (parseFloat(input.value) || 1) + d));
        input.dispatchEvent(new Event('change', { bubbles: true }));
      });
      return b;
    };
    q.prepend(mk(-1, '−'));
    q.append(mk(1, '+'));
  });

  /* ---------------- sticky add to cart (mobile) ---------------- */
  const cartForm = $('.single-product form.cart');
  const sticky = $('#stickyAtc');
  if (cartForm && sticky && 'IntersectionObserver' in window) {
    new IntersectionObserver(([en]) => sticky.classList.toggle('show', !en.isIntersecting && en.boundingClientRect.top < 0)).observe(cartForm);
    $('button', sticky)?.addEventListener('click', () => {
      cartForm.scrollIntoView({ behavior: reduced ? 'auto' : 'smooth', block: 'center' });
    });
  }

  // Rating link opens the Reviews tab.
  $$('.js-open-reviews').forEach((a) => a.addEventListener('click', () => {
    $('#tab-title-reviews a')?.click();
  }));

  /* ---------------- size guide: tabs & units ---------------- */
  $$('.sg__bar .tabs').forEach((tabs) => {
    tabs.addEventListener('click', (e) => {
      const b = e.target.closest('button');
      if (!b) return;
      $$('button', tabs).forEach((x) => x.classList.toggle('active', x === b));
      $$('.sg__panel').forEach((p) => p.classList.toggle('active', p.dataset.panel === b.dataset.tab));
    });
  });
  const toCm = (txt) => txt.replace(/\d+(\.\d+)?/g, (n) => Math.round(parseFloat(n) * 2.54));
  $$('.unit-switch').forEach((sw) => {
    sw.addEventListener('click', (e) => {
      const b = e.target.closest('button');
      if (!b) return;
      $$('.unit-switch button').forEach((x) => x.classList.toggle('active', x.dataset.unit === b.dataset.unit));
      $$('.size-table.has-units td[data-in]').forEach((td) => {
        td.textContent = b.dataset.unit === 'cm' ? toCm(td.dataset.in) : td.dataset.in;
      });
    });
  });

  /* ---------------- policy: contents list ---------------- */
  const body = $('#policyBody');
  const toc = $('#toc');
  if (body && toc) {
    const heads = $$('h2', body);
    if (!heads.length) toc.closest('aside').hidden = true;
    heads.forEach((h, i) => {
      if (!h.id) h.id = 'sec-' + (i + 1);
      const li = document.createElement('li');
      li.innerHTML = `<a href="#${h.id}"></a>`;
      li.firstChild.textContent = h.textContent;
      toc.appendChild(li);
    });
    if ('IntersectionObserver' in window) {
      const links = $$('a', toc);
      const obs = new IntersectionObserver((entries) => {
        entries.forEach((en) => {
          if (en.isIntersecting) links.forEach((a) => a.classList.toggle('active', a.getAttribute('href') === '#' + en.target.id));
        });
      }, { rootMargin: '-20% 0px -70% 0px' });
      heads.forEach((h) => obs.observe(h));
    }
  }
})();

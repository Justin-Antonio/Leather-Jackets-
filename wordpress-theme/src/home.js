/**
 * Home page: hero intro, scroll-driven detail tour, lazy-loaded WebGL jacket.
 */
import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

gsap.registerPlugin(ScrollTrigger);

const $ = (s, el = document) => el.querySelector(s);
const $$ = (s, el = document) => [...el.querySelectorAll(s)];
const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
const products = window.HA_HERO || [];

let hero = null;
const poster = $('#stagePoster');
const canvas = $('#webgl');
const hotspots = $$('.hotspot');
const hotWrap = $('#hotspots');

/* ---------------- hero text intro ---------------- */
if (!reduced) {
  gsap.from('.hero__title .line > span', { yPercent: 110, duration: 1.1, stagger: 0.12, ease: 'expo.out', delay: 0.1 });
  gsap.from('.reveal-hero', { y: 26, opacity: 0, duration: 0.9, stagger: 0.08, ease: 'power3.out', delay: 0.35 });
  if (poster) gsap.from(poster, { opacity: 0, scale: 0.96, duration: 1.2, ease: 'power3.out' });
}

/* ---------------- product picker ---------------- */
const picker = $('#picker');
function select(i) {
  const p = products[i];
  if (!p) return;
  $$('.picker__item').forEach((b, k) => {
    b.classList.toggle('active', k === i);
    b.setAttribute('aria-selected', k === i ? 'true' : 'false');
  });
  $('#pickerName').textContent = p.name;
  $('#pickerPrice').textContent = p.price;
  $('#pickerInfo').href = p.url;
  if (hero) hero.show(i);
  else if (poster) poster.src = p.image;
}
picker?.addEventListener('click', (e) => {
  const b = e.target.closest('.picker__item');
  if (b) select(+b.dataset.index);
});
$('#replayBtn')?.addEventListener('click', () => {
  window.scrollTo({ top: 0, behavior: reduced ? 'auto' : 'smooth' });
  hero?.replay();
});

/* ---------------- scroll ---------------- */
ScrollTrigger.create({
  trigger: '#hero', start: 'top top', end: 'bottom top', scrub: true,
  onUpdate: (st) => hero?.setHeroProgress(st.progress),
});
gsap.to('.hero__content, .hero__hint, .scroll-cue', {
  opacity: 0, y: -70, ease: 'none',
  scrollTrigger: { trigger: '#hero', start: 'top top', end: '55% top', scrub: true },
});
if (poster) {
  gsap.to(poster, { opacity: 0, ease: 'none', scrollTrigger: { trigger: '#hero', start: 'top top', end: '70% top', scrub: true } });
}

const steps = $$('#tourSteps li');
const keys = ['hide', 'stitch', 'hardware', 'collar'];
ScrollTrigger.create({
  trigger: '#tour', start: 'top top', end: '+=240%', pin: true, scrub: true,
  onUpdate: (st) => {
    hero?.setTourProgress(st.progress);
    $('#tourBar').style.width = `${st.progress * 100}%`;
    const idx = Math.min(steps.length - 1, Math.floor(st.progress * steps.length * 0.999));
    steps.forEach((s, i) => s.classList.toggle('active', i === idx));
    hotspots.forEach((h) => h.classList.toggle('focus', h.dataset.key === keys[idx]));
  },
});
gsap.from('.tour__panel', {
  x: -50, opacity: 0, ease: 'power2.out',
  scrollTrigger: { trigger: '#tour', start: 'top 80%', end: 'top top', scrub: true },
});

// Stop rendering once the stage is covered by the page below.
const stage = $('#stage');
ScrollTrigger.create({
  trigger: '.marquee', start: 'top top',
  onEnter: () => { hero?.setActive(false); stage.style.visibility = 'hidden'; hotWrap.style.visibility = 'hidden'; },
  onLeaveBack: () => { hero?.setActive(true); stage.style.visibility = ''; hotWrap.style.visibility = ''; },
});

/* ---------------- WebGL (lazy) ---------------- */
function supportsWebGL() {
  try {
    const c = document.createElement('canvas');
    return !!(c.getContext('webgl2') || c.getContext('webgl'));
  } catch {
    return false;
  }
}

async function startHero() {
  if (!products.length || !supportsWebGL()) return;
  try {
    const { createHero } = await import('./hero3d.js');
    hero = createHero(canvas, {
      products,
      reducedMotion: reduced,
      onReady: () => {
        document.body.classList.add('webgl-ready');
        const i = +($('.picker__item.active')?.dataset.index || 0);
        if (i) hero.show(i);
      },
      onFrame: ({ anchors, zoom }) => {
        hotWrap.style.opacity = Math.min(1, zoom * 1.4);
        hotspots.forEach((h) => {
          const a = anchors[h.dataset.key];
          if (!a) return;
          h.style.transform = `translate3d(${a.x.toFixed(1)}px, ${a.y.toFixed(1)}px, 0)`;
          h.classList.toggle('left', a.x > window.innerWidth * 0.62);
        });
      },
    });
  } catch (err) {
    console.warn('3D hero unavailable, showing the photo instead.', err);
  }
}

// Load the 3D code after the page is painted so it never delays the first view.
const idle = window.requestIdleCallback || ((cb) => setTimeout(cb, 200));
if (document.readyState === 'complete') idle(startHero);
else window.addEventListener('load', () => idle(startHero), { once: true });

window.addEventListener('load', () => ScrollTrigger.refresh());

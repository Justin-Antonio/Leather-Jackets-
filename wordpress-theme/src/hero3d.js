/**
 * Real-photo 3D jacket.
 *
 * The product photo itself is rendered in WebGL: its background is removed in
 * the browser, a depth map is built from the jacket's silhouette (so the
 * garment gets real volume), and a leather-sheen light follows the pointer.
 * A "sewing" line reveals the jacket seam by seam, and products switch with a
 * burn-away / stitch-in transition.
 */
import {
  WebGLRenderer, Scene, PerspectiveCamera, Group, Mesh, PlaneGeometry, ShaderMaterial,
  DataTexture, RGBAFormat, UnsignedByteType, LinearFilter, Texture, SRGBColorSpace,
  Vector2, Vector3, Color, CanvasTexture, BufferGeometry, BufferAttribute, Points,
  PointsMaterial, NoToneMapping, ClampToEdgeWrapping,
} from 'three';
import gsap from 'gsap';

const lerp = (a, b, t) => a + (b - a) * t;
const clamp01 = (x) => Math.min(1, Math.max(0, x));
const smooth = (a, b, x) => {
  const t = clamp01((x - a) / (b - a));
  return t * t * (3 - 2 * t);
};

/* ------------------------------------------------------------------ */
/* Image analysis: background removal + depth map                       */
/* ------------------------------------------------------------------ */

function loadImage(src) {
  return new Promise((resolve, reject) => {
    const img = new Image();
    img.crossOrigin = 'anonymous';
    img.decoding = 'async';
    img.onload = () => resolve(img);
    img.onerror = reject;
    img.src = src;
  });
}

function boxBlur(src, w, h, r, passes = 1) {
  let a = src, b = new Float32Array(w * h);
  for (let p = 0; p < passes; p++) {
    // horizontal
    for (let y = 0; y < h; y++) {
      let acc = 0;
      const row = y * w;
      for (let x = -r; x <= r; x++) acc += a[row + Math.min(w - 1, Math.max(0, x))];
      for (let x = 0; x < w; x++) {
        b[row + x] = acc / (2 * r + 1);
        acc += a[row + Math.min(w - 1, x + r + 1)] - a[row + Math.max(0, x - r)];
      }
    }
    // vertical
    const c = new Float32Array(w * h);
    for (let x = 0; x < w; x++) {
      let acc = 0;
      for (let y = -r; y <= r; y++) acc += b[Math.min(h - 1, Math.max(0, y)) * w + x];
      for (let y = 0; y < h; y++) {
        c[y * w + x] = acc / (2 * r + 1);
        acc += b[Math.min(h - 1, y + r + 1) * w + x] - b[Math.max(0, y - r) * w + x];
      }
    }
    a = c;
  }
  return a;
}

export function analyseImage(img, W = 360) {
  const H = Math.round((W * img.naturalHeight) / img.naturalWidth);
  const cv = document.createElement('canvas');
  cv.width = W;
  cv.height = H;
  const ctx = cv.getContext('2d', { willReadFrequently: true });
  ctx.drawImage(img, 0, 0, W, H);
  const px = ctx.getImageData(0, 0, W, H).data;
  const N = W * H;

  // Background colour = median of the border pixels.
  const border = [];
  for (let x = 0; x < W; x++) border.push(x, (H - 1) * W + x);
  for (let y = 0; y < H; y++) border.push(y * W, y * W + W - 1);
  const chan = (c) => border.map((i) => px[i * 4 + c]).sort((a, b) => a - b)[border.length >> 1];
  const bg = [chan(0), chan(1), chan(2)];

  const isBg = (i) => {
    const r = px[i * 4], g = px[i * 4 + 1], b = px[i * 4 + 2];
    const mx = Math.max(r, g, b), mn = Math.min(r, g, b);
    const lum = 0.299 * r + 0.587 * g + 0.114 * b;
    const d = Math.max(Math.abs(r - bg[0]), Math.abs(g - bg[1]), Math.abs(b - bg[2]));
    return (d < 34 || (mx - mn < 20 && lum > 170 && d < 70));
  };

  // Flood fill from the border: only background connected to the edge is removed,
  // so white T-shirts or shiny highlights inside the jacket are kept.
  const bgm = new Uint8Array(N);
  const q = new Int32Array(N);
  let qh = 0, qt = 0;
  for (const i of border) if (!bgm[i] && isBg(i)) { bgm[i] = 1; q[qt++] = i; }
  while (qh < qt) {
    const i = q[qh++];
    const x = i % W, y = (i / W) | 0;
    if (x > 0 && !bgm[i - 1] && isBg(i - 1)) { bgm[i - 1] = 1; q[qt++] = i - 1; }
    if (x < W - 1 && !bgm[i + 1] && isBg(i + 1)) { bgm[i + 1] = 1; q[qt++] = i + 1; }
    if (y > 0 && !bgm[i - W] && isBg(i - W)) { bgm[i - W] = 1; q[qt++] = i - W; }
    if (y < H - 1 && !bgm[i + W] && isBg(i + W)) { bgm[i + W] = 1; q[qt++] = i + W; }
  }

  // Jacket mask, eroded by one pixel to drop the light halo of the old background.
  const m = new Uint8Array(N);
  for (let i = 0; i < N; i++) m[i] = bgm[i] ? 0 : 1;
  const me = new Uint8Array(N);
  for (let y = 1; y < H - 1; y++) {
    for (let x = 1; x < W - 1; x++) {
      const i = y * W + x;
      me[i] = m[i] && m[i - 1] && m[i + 1] && m[i - W] && m[i + W] ? 1 : 0;
    }
  }

  // Chamfer distance to the silhouette edge.
  const INF = 1e9;
  const dist = new Float32Array(N);
  for (let i = 0; i < N; i++) dist[i] = me[i] ? INF : 0;
  const D1 = 1, D2 = 1.4142;
  for (let y = 0; y < H; y++) {
    for (let x = 0; x < W; x++) {
      const i = y * W + x;
      if (!dist[i]) continue;
      let d = dist[i];
      if (x > 0) d = Math.min(d, dist[i - 1] + D1);
      if (y > 0) d = Math.min(d, dist[i - W] + D1);
      if (x > 0 && y > 0) d = Math.min(d, dist[i - W - 1] + D2);
      if (x < W - 1 && y > 0) d = Math.min(d, dist[i - W + 1] + D2);
      dist[i] = d;
    }
  }
  for (let y = H - 1; y >= 0; y--) {
    for (let x = W - 1; x >= 0; x--) {
      const i = y * W + x;
      if (!dist[i]) continue;
      let d = dist[i];
      if (x < W - 1) d = Math.min(d, dist[i + 1] + D1);
      if (y < H - 1) d = Math.min(d, dist[i + W] + D1);
      if (x < W - 1 && y < H - 1) d = Math.min(d, dist[i + W + 1] + D2);
      if (x > 0 && y < H - 1) d = Math.min(d, dist[i + W - 1] + D2);
      dist[i] = d;
    }
  }

  // Inflated depth (rounded like a real garment) + fine relief from the photo's shading.
  const R = W * 0.13;
  const lum = new Float32Array(N);
  for (let i = 0; i < N; i++) lum[i] = (0.299 * px[i * 4] + 0.587 * px[i * 4 + 1] + 0.114 * px[i * 4 + 2]) / 255;
  const lumBlur = boxBlur(lum, W, H, 7, 2);
  let depth = new Float32Array(N);
  let minX = W, minY = H, maxX = 0, maxY = 0;
  for (let i = 0; i < N; i++) {
    if (!me[i]) continue;
    const d = Math.min(dist[i] / R, 1);
    const inflate = Math.sqrt(1 - (1 - d) * (1 - d));
    depth[i] = clamp01(inflate * 0.85 + (lum[i] - lumBlur[i]) * 0.5 + 0.06);
    const x = i % W, y = (i / W) | 0;
    if (x < minX) minX = x; if (x > maxX) maxX = x;
    if (y < minY) minY = y; if (y > maxY) maxY = y;
  }
  depth = boxBlur(depth, W, H, 2, 2);
  const soft = boxBlur(Float32Array.from(me), W, H, 1, 1);

  const data = new Uint8Array(N * 4);
  for (let i = 0; i < N; i++) {
    data[i * 4] = Math.round(depth[i] * 255 * (me[i] ? 1 : 0.4));
    data[i * 4 + 1] = Math.round(soft[i] * 255);
    data[i * 4 + 2] = Math.round(Math.min(dist[i] / 7, 1) * 255);
    data[i * 4 + 3] = 255;
  }
  const tex = new DataTexture(data, W, H, RGBAFormat, UnsignedByteType);
  tex.magFilter = LinearFilter;
  tex.minFilter = LinearFilter;
  tex.wrapS = tex.wrapT = ClampToEdgeWrapping;
  tex.needsUpdate = true;

  return {
    tex,
    aspect: img.naturalWidth / img.naturalHeight,
    texel: new Vector2(1 / W, 1 / H),
    // bounding box in UV space (v = 0 at the bottom)
    bbox: { u0: minX / W, u1: maxX / W, v0: 1 - maxY / H, v1: 1 - minY / H },
  };
}

/* ------------------------------------------------------------------ */
/* Shaders                                                              */
/* ------------------------------------------------------------------ */

const vert = /* glsl */ `
  uniform sampler2D uData;
  uniform float uDepth;
  varying vec2 vUv;
  varying vec3 vT; varying vec3 vB; varying vec3 vN;
  varying vec3 vView;
  void main(){
    vUv = uv;
    vec4 d = texture2D(uData, vec2(uv.x, 1.0 - uv.y));
    vec3 p = position + vec3(0.0, 0.0, d.r * uDepth);
    vT = normalize(normalMatrix * vec3(1.0, 0.0, 0.0));
    vB = normalize(normalMatrix * vec3(0.0, 1.0, 0.0));
    vN = normalize(normalMatrix * vec3(0.0, 0.0, 1.0));
    vec4 mv = modelViewMatrix * vec4(p, 1.0);
    vView = -mv.xyz;
    gl_Position = projectionMatrix * mv;
  }`;

const frag = /* glsl */ `
  uniform sampler2D uMap;
  uniform sampler2D uData;
  uniform vec2 uTexel;
  uniform float uReveal;
  uniform float uOutline;
  uniform float uOutlineAlpha;
  uniform float uDissolve;
  uniform float uTime;
  uniform float uSheen;
  uniform float uRelief;
  uniform vec3 uLight;
  uniform vec3 uThread;
  varying vec2 vUv;
  varying vec3 vT; varying vec3 vB; varying vec3 vN;
  varying vec3 vView;

  float hash(vec2 p){ return fract(sin(dot(p, vec2(127.1, 311.7))) * 43758.5453); }
  float noise(vec2 p){
    vec2 i = floor(p), f = fract(p);
    vec2 u = f * f * (3.0 - 2.0 * f);
    return mix(mix(hash(i), hash(i + vec2(1, 0)), u.x), mix(hash(i + vec2(0, 1)), hash(i + vec2(1, 1)), u.x), u.y);
  }

  void main(){
    vec2 duv = vec2(vUv.x, 1.0 - vUv.y);
    vec4 d = texture2D(uData, duv);
    float mask = d.g;
    if (mask < 0.03) discard;

    // Sewing line travelling from hem to collar
    float wob = sin(vUv.x * 42.0 + uTime * 3.0) * 0.004 + (noise(vec2(vUv.x * 24.0, uTime)) - 0.5) * 0.012;
    float line = uReveal * 1.14 - 0.07 + wob;
    if (vUv.y > line) discard;

    // Burn-away when switching products
    float n = noise(vUv * 9.0) * 0.7 + noise(vUv * 31.0) * 0.3;
    if (n < uDissolve) discard;

    vec3 col = texture2D(uMap, vUv).rgb;

    // Relief lighting from the depth map (relative to a flat photo, so colours stay true)
    float dx = texture2D(uData, duv + vec2(uTexel.x, 0.0)).r - texture2D(uData, duv - vec2(uTexel.x, 0.0)).r;
    float dy = texture2D(uData, duv + vec2(0.0, uTexel.y)).r - texture2D(uData, duv - vec2(0.0, uTexel.y)).r;
    vec3 no = normalize(vec3(-dx * uRelief, dy * uRelief, 1.0));
    vec3 N = normalize(vT * no.x + vB * no.y + vN * no.z);
    vec3 L = normalize(uLight);
    vec3 V = normalize(vView);
    vec3 H = normalize(L + V);
    float shade = dot(N, L) - dot(vN, L);
    col *= 1.0 + shade * 0.4;

    // Leather sheen: a moving glossy highlight, stronger on darker leather
    float lum = dot(col, vec3(0.299, 0.587, 0.114));
    float spec = pow(max(dot(N, H), 0.0), 60.0) * uSheen * smoothstep(0.03, 0.25, lum) * (1.0 - 0.5 * lum);
    col += vec3(1.0, 0.93, 0.84) * spec * 0.32;

    // Rim light on the silhouette
    float rim = pow(1.0 - max(dot(N, V), 0.0), 3.0);
    col += vec3(1.0, 0.85, 0.65) * rim * 0.04 * smoothstep(0.03, 0.2, lum);

    // Glowing thread at the sewing line
    float below = line - vUv.y;
    float sewing = 1.0 - step(0.999, uReveal);
    float dash = step(0.42, fract(vUv.x * 64.0 - uTime * 1.8));
    col = mix(col, uThread, smoothstep(0.016, 0.0, below) * dash * sewing);
    col += vec3(1.0, 0.72, 0.38) * smoothstep(0.07, 0.0, below) * 0.35 * sewing;

    // Hand-stitched outline around the silhouette
    float edge = smoothstep(0.62, 0.42, d.b) * smoothstep(0.1, 0.25, d.b);
    float ang = atan(vUv.y - 0.5, vUv.x - 0.5) / 6.28318 + 0.5;
    float traced = step(ang, uOutline);
    float dash2 = step(0.5, fract((vUv.x * 0.8 + vUv.y) * 150.0));
    col = mix(col, uThread, edge * dash2 * traced * uOutlineAlpha);

    // Ember edge while burning away
    col += vec3(1.0, 0.55, 0.2) * smoothstep(uDissolve + 0.05, uDissolve, n) * step(0.001, uDissolve) * 1.6;

    gl_FragColor = vec4(col, smoothstep(0.03, 0.5, mask));
    #include <colorspace_fragment>
  }`;

/* ------------------------------------------------------------------ */
/* Scene                                                                */
/* ------------------------------------------------------------------ */

function radialTexture(stops) {
  const c = document.createElement('canvas');
  c.width = c.height = 128;
  const g = c.getContext('2d');
  const grd = g.createRadialGradient(64, 64, 0, 64, 64, 64);
  stops.forEach(([o, col]) => grd.addColorStop(o, col));
  g.fillStyle = grd;
  g.fillRect(0, 0, 128, 128);
  const t = new CanvasTexture(c);
  t.colorSpace = SRGBColorSpace;
  return t;
}

// Tour stops: [x, y] inside the jacket's bounding box (0..1, y from the bottom)
const TOUR = {
  hide: [0.3, 0.52],
  stitch: [0.84, 0.62],
  hardware: [0.5, 0.42],
  collar: [0.5, 0.93],
};
const TOUR_ORDER = ['hide', 'stitch', 'hardware', 'collar'];

export function createHero(canvas, { products, onFrame, onReady, reducedMotion = false }) {
  const mobile = window.matchMedia('(max-width: 700px)').matches;
  const renderer = new WebGLRenderer({ canvas, antialias: !mobile, alpha: true, powerPreference: 'high-performance' });
  renderer.setPixelRatio(Math.min(window.devicePixelRatio, mobile ? 1.75 : 2));
  renderer.toneMapping = NoToneMapping;
  renderer.setClearColor(0x000000, 0);

  const scene = new Scene();
  const camera = new PerspectiveCamera(30, 1, 0.1, 50);
  camera.position.set(0, 0, 8);

  const root = new Group(); // layout
  const tilt = new Group(); // user rotation
  root.add(tilt);
  scene.add(root);

  const PH = 3.3; // jacket height in world units
  const segX = mobile ? 110 : 170;
  const segY = mobile ? 140 : 210;

  const shadow = new Mesh(
    new PlaneGeometry(1, 1),
    new ShaderMaterial({
      transparent: true,
      depthWrite: false,
      uniforms: { uMap: { value: radialTexture([[0, 'rgba(40,22,10,0.55)'], [0.55, 'rgba(40,22,10,0.16)'], [1, 'rgba(40,22,10,0)']]) }, uOpacity: { value: 0 } },
      vertexShader: 'varying vec2 vUv; void main(){ vUv = uv; gl_Position = projectionMatrix * modelViewMatrix * vec4(position,1.0); }',
      fragmentShader: 'uniform sampler2D uMap; uniform float uOpacity; varying vec2 vUv; void main(){ vec4 c = texture2D(uMap, vUv); gl_FragColor = vec4(c.rgb, c.a * uOpacity); }',
    }),
  );
  shadow.rotation.x = -Math.PI / 2;
  shadow.position.y = -PH / 2 - 0.12;
  shadow.scale.set(2.6, 0.9, 1);
  root.add(shadow);

  // Floating dust (warm, subtle)
  const dustCount = mobile ? 70 : 140;
  const dp = new Float32Array(dustCount * 3);
  const dseed = new Float32Array(dustCount);
  for (let i = 0; i < dustCount; i++) {
    dp[i * 3] = (Math.random() - 0.5) * 8;
    dp[i * 3 + 1] = (Math.random() - 0.5) * 5;
    dp[i * 3 + 2] = (Math.random() - 0.5) * 3;
    dseed[i] = Math.random();
  }
  const dustGeo = new BufferGeometry();
  dustGeo.setAttribute('position', new BufferAttribute(dp, 3));
  const dust = new Points(
    dustGeo,
    new PointsMaterial({
      size: 0.04, transparent: true, depthWrite: false, opacity: 0.55,
      map: radialTexture([[0, 'rgba(176,112,58,1)'], [1, 'rgba(176,112,58,0)']]),
    }),
  );
  scene.add(dust);

  const threadColor = new Color('#c9954f');
  const light = new Vector3(-0.4, 0.5, 1);
  const state = {
    heroP: 0, tourP: 0, active: true,
    drag: 0, dragVel: 0, pointer: new Vector2(), sheen: 1,
    zoom: 1, focus: new Vector2(), rotY: 0,
    layout: {}, index: 0,
  };
  const cache = new Map();
  let current = null; // { mesh, info }

  function makeMesh(info, texture) {
    const w = PH * info.aspect;
    const geo = new PlaneGeometry(w, PH, segX, segY);
    const mat = new ShaderMaterial({
      vertexShader: vert,
      fragmentShader: frag,
      transparent: true,
      uniforms: {
        uMap: { value: texture },
        uData: { value: info.tex },
        uTexel: { value: info.texel },
        uDepth: { value: 0.55 },
        uRelief: { value: 4.0 },
        uReveal: { value: 0 },
        uOutline: { value: 0 },
        uOutlineAlpha: { value: 1 },
        uDissolve: { value: 0 },
        uTime: { value: 0 },
        uSheen: { value: 1 },
        uLight: { value: light },
        uThread: { value: threadColor },
      },
    });
    const mesh = new Mesh(geo, mat);
    mesh.position.z = -0.25; // depth pushes the front forward, keep the pivot centred
    return mesh;
  }

  async function prepare(index) {
    if (cache.has(index)) return cache.get(index);
    const p = products[index];
    const promise = loadImage(p.image).then((img) => {
      const info = analyseImage(img, mobile ? 300 : 380);
      const texture = new Texture(img);
      texture.colorSpace = SRGBColorSpace;
      texture.anisotropy = 4;
      texture.needsUpdate = true;
      return { info, texture, mesh: makeMesh(info, texture) };
    });
    cache.set(index, promise);
    return promise;
  }

  function uniforms(entry) {
    return entry.mesh.material.uniforms;
  }

  function sew(entry, delay = 0) {
    const u = uniforms(entry);
    const tl = gsap.timeline({ delay });
    if (reducedMotion) {
      u.uReveal.value = 1;
      u.uOutline.value = 1;
      u.uOutlineAlpha.value = 0;
      return tl;
    }
    u.uReveal.value = 0;
    u.uOutline.value = 0;
    u.uOutlineAlpha.value = 1;
    tl.to(u.uOutline, { value: 1, duration: 1.1, ease: 'power2.inOut' }, 0)
      .to(u.uReveal, { value: 1, duration: 1.9, ease: 'power2.inOut' }, 0.45)
      .to(u.uOutlineAlpha, { value: 0, duration: 0.9, ease: 'power1.out' }, 2.1)
      .fromTo(light, { x: -1.4 }, { x: 0.9, duration: 1.6, ease: 'power2.inOut' }, 1.6)
      .to(light, { x: -0.4, duration: 1.2, ease: 'power2.inOut' }, 3.2)
      .fromTo(state, { rotY: -0.55 }, { rotY: 0, duration: 2.6, ease: 'power3.out' }, 0);
    return tl;
  }

  async function show(index, first = false) {
    const entry = await prepare(index);
    state.index = index;
    const prev = current;
    current = entry;
    uniforms(entry).uDissolve.value = 0;
    uniforms(entry).uReveal.value = 0;
    tilt.add(entry.mesh);
    if (prev && prev !== entry) {
      const pu = uniforms(prev);
      gsap.to(pu.uDissolve, {
        value: 1.05, duration: reducedMotion ? 0.01 : 0.75, ease: 'power2.in',
        onComplete: () => { tilt.remove(prev.mesh); pu.uDissolve.value = 0; },
      });
    }
    gsap.to(shadow.material.uniforms.uOpacity, { value: 1, duration: 1.2 });
    sew(entry, prev ? 0.35 : 0);
    if (first && onReady) onReady();
    // warm the next image in the background
    if (products.length > 1) prepare((index + 1) % products.length);
  }

  /* interaction */
  let dragging = false, lastX = 0, startX = 0, startY = 0, horizontal = null;
  canvas.addEventListener('pointerdown', (e) => {
    dragging = true; lastX = startX = e.clientX; startY = e.clientY; horizontal = null;
  });
  window.addEventListener('pointermove', (e) => {
    state.pointer.set((e.clientX / window.innerWidth) * 2 - 1, -(e.clientY / window.innerHeight) * 2 + 1);
    if (!dragging) return;
    if (horizontal === null && Math.abs(e.clientX - startX) + Math.abs(e.clientY - startY) > 6) {
      horizontal = Math.abs(e.clientX - startX) > Math.abs(e.clientY - startY);
    }
    if (horizontal) {
      state.dragVel = (e.clientX - lastX) * 0.006;
      state.drag = Math.max(-0.75, Math.min(0.75, state.drag + state.dragVel));
      canvas.classList.add('grabbing');
    }
    lastX = e.clientX;
  });
  window.addEventListener('pointerup', () => { dragging = false; canvas.classList.remove('grabbing'); });

  /* layout */
  function resize() {
    const w = window.innerWidth, h = window.innerHeight;
    renderer.setSize(w, h, false);
    camera.aspect = w / h;
    camera.updateProjectionMatrix();
    const narrow = w / h < 0.95;
    const viewH = 2 * camera.position.z * Math.tan((camera.fov * Math.PI) / 360);
    const viewW = viewH * camera.aspect;
    state.layout = narrow
      ? { x: 0, y: -viewH * 0.2, s: Math.min(0.74, (viewW * 0.82) / (PH * 0.95)), tourX: 0, tourY: -viewH * 0.14 }
      : { x: viewW * 0.2, y: -0.1, s: 1, tourX: viewW * 0.16, tourY: 0 };
  }
  window.addEventListener('resize', resize);
  resize();

  /* loop */
  const v = new Vector3();
  const out = {};
  let last = performance.now();
  function frame(now) {
    requestAnimationFrame(frame);
    if (!state.active) { last = now; return; }
    const dt = Math.min((now - last) / 1000, 0.05);
    last = now;
    const t = now / 1000;
    const L = state.layout;

    // tour: zoom towards a detail per step
    const tp = state.tourP;
    const stepF = clamp01(tp) * (TOUR_ORDER.length - 0.001);
    const si = Math.floor(stepF);
    const zoomIn = smooth(0.02, 0.12, tp) * (1 - smooth(0.9, 1, tp));
    let fx = 0.5, fy = 0.5;
    if (current) {
      const b = current.info.bbox;
      const k = smooth(0.55, 0.95, stepF - si);
      const a = TOUR[TOUR_ORDER[si]], nx = TOUR[TOUR_ORDER[Math.min(si + 1, TOUR_ORDER.length - 1)]];
      fx = lerp(b.u0, b.u1, lerp(a[0], nx[0], k));
      fy = lerp(b.v0, b.v1, lerp(a[1], nx[1], k));
    }
    const ease = 1 - Math.exp(-dt * 5);
    state.focus.x += (fx - state.focus.x) * ease;
    state.focus.y += (fy - state.focus.y) * ease;
    const targetZoom = lerp(1, mobile ? 1.35 : 1.45, zoomIn);
    state.zoom += (targetZoom - state.zoom) * ease;

    const h = smooth(0, 1, state.heroP);
    const baseS = lerp(L.s, L.s * 0.92, h);
    const S = baseS * state.zoom;
    root.scale.setScalar(S);
    // keep the focused detail near the tour anchor while zoomed
    const w = current ? PH * current.info.aspect : PH;
    const lx = (state.focus.x - 0.5) * w, ly = (state.focus.y - 0.5) * PH;
    const baseX = lerp(L.x, L.tourX, h), baseY = lerp(L.y, L.tourY, h);
    const zf = (state.zoom - 1) / 0.45;
    root.position.x = baseX - lx * S * zf * (mobile ? 0.55 : 0.9);
    root.position.y = baseY - ly * S * zf * 0.9 + Math.sin(t * 0.9) * 0.025;

    if (!dragging) {
      state.dragVel *= 0.9;
      state.drag *= 0.985; // ease back towards the front
    }
    const idle = Math.sin(t * 0.45) * 0.12 * (1 - zoomIn);
    tilt.rotation.y = state.rotY + state.drag + idle + state.pointer.x * 0.12 + (current ? (fx - 0.5) * -0.5 * zoomIn : 0);
    tilt.rotation.x = -state.pointer.y * 0.06 + (fy - 0.5) * 0.25 * zoomIn;

    // light follows the pointer for a moving leather sheen
    light.x += ((state.pointer.x * 1.1 - 0.2) - light.x) * 0.04;
    light.y += ((state.pointer.y * 0.8 + 0.5) - light.y) * 0.04;

    if (current) uniforms(current).uTime.value = t;
    for (const entry of tilt.children) if (entry.material?.uniforms) entry.material.uniforms.uTime.value = t;

    const arr = dust.geometry.attributes.position.array;
    for (let i = 0; i < dustCount; i++) {
      arr[i * 3 + 1] += dt * (0.04 + dseed[i] * 0.07);
      arr[i * 3] += Math.sin(t * 0.5 + dseed[i] * 10) * dt * 0.02;
      if (arr[i * 3 + 1] > 2.6) arr[i * 3 + 1] = -2.6;
    }
    dust.geometry.attributes.position.needsUpdate = true;

    renderer.render(scene, camera);

    if (onFrame && current) {
      const b = current.info.bbox;
      const W = window.innerWidth, Hh = window.innerHeight;
      for (const key of TOUR_ORDER) {
        const [px, py] = TOUR[key];
        v.set((lerp(b.u0, b.u1, px) - 0.5) * w, (lerp(b.v0, b.v1, py) - 0.5) * PH, 0.35);
        current.mesh.localToWorld(v);
        v.project(camera);
        out[key] = { x: (v.x * 0.5 + 0.5) * W, y: (-v.y * 0.5 + 0.5) * Hh };
      }
      onFrame({ anchors: out, step: TOUR_ORDER[si], zoom: zoomIn });
    }
  }
  requestAnimationFrame(frame);

  show(0, true);

  return {
    show: (i) => show(i),
    replay: () => current && sew(current),
    setHeroProgress: (p) => (state.heroP = p),
    setTourProgress: (p) => (state.tourP = p),
    setActive: (a) => (state.active = a),
    get index() { return state.index; },
  };
}

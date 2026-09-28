import * as THREE from 'three';
import { RoomEnvironment } from 'three/examples/jsm/environments/RoomEnvironment.js';
import gsap from 'gsap';

/* ------------------------------------------------------------------ *
 *  Procedural 3D leather jacket on a tailor's dress form.
 *  Everything (panels, sleeves, stitching, zippers, textures) is
 *  generated in code, so no heavy model files need to be loaded.
 * ------------------------------------------------------------------ */

const TAU = Math.PI * 2;
const DEPTH = 0.7; // torso front-to-back squash (ellipse)
const OFF = 0.035; // jacket shell distance from the dress form
const HEM = 0.075; // t where the waistband ends
const SIDE = 1.45; // phi of the side seam
const V_START = 0.64; // t where the neckline opening begins

const lerp = (a, b, t) => a + (b - a) * t;
const clamp01 = (x) => Math.min(1, Math.max(0, x));
const smooth = (a, b, x) => {
  const t = clamp01((x - a) / (b - a));
  return t * t * (3 - 2 * t);
};

export const LEATHER_COLORS = {
  cognac: { base: '#7c3f1d', thread: '#e6c088', label: 'Cognac' },
  black: { base: '#161414', thread: '#9a9084', label: 'Midnight Black' },
  camel: { base: '#a0703f', thread: '#f4dcae', label: 'Camel' },
  tobacco: { base: '#5e3820', thread: '#d9b07a', label: 'Tobacco' },
};

/* ------------------------------ textures ------------------------------ */

function hash2(x, y, seed = 0) {
  const s = Math.sin(x * 127.1 + y * 311.7 + seed * 74.7) * 43758.5453;
  return s - Math.floor(s);
}

function valueNoise(x, y, period, seed) {
  const xi = Math.floor(x), yi = Math.floor(y);
  const xf = x - xi, yf = y - yi;
  const u = xf * xf * (3 - 2 * xf), v = yf * yf * (3 - 2 * yf);
  const w = (a) => ((a % period) + period) % period;
  const a = hash2(w(xi), w(yi), seed), b = hash2(w(xi + 1), w(yi), seed);
  const c = hash2(w(xi), w(yi + 1), seed), d = hash2(w(xi + 1), w(yi + 1), seed);
  return lerp(lerp(a, b, u), lerp(c, d, u), v);
}

function makeLeatherTextures(size = 512) {
  const cells = 34;
  const pts = [];
  for (let y = 0; y < cells; y++)
    for (let x = 0; x < cells; x++) pts.push([x + hash2(x, y, 1) * 0.9, y + hash2(x, y, 2) * 0.9]);

  const h = new Float32Array(size * size);
  const mott = new Float32Array(size * size);
  for (let py = 0; py < size; py++) {
    for (let px = 0; px < size; px++) {
      const gx = (px / size) * cells, gy = (py / size) * cells;
      const cx = Math.floor(gx), cy = Math.floor(gy);
      let f1 = 9, f2 = 9;
      for (let oy = -1; oy <= 1; oy++) {
        for (let ox = -1; ox <= 1; ox++) {
          const nx = cx + ox, ny = cy + oy;
          const p = pts[((ny + cells) % cells) * cells + ((nx + cells) % cells)];
          const dx = nx - cx + (p[0] - Math.floor(p[0])) + cx - gx;
          const dy = ny - cy + (p[1] - Math.floor(p[1])) + cy - gy;
          const d = Math.sqrt(dx * dx + dy * dy);
          if (d < f1) { f2 = f1; f1 = d; } else if (d < f2) f2 = d;
        }
      }
      const pebble = 1 - Math.exp(-(f2 - f1) * 5.5);
      const fine = valueNoise(px / 4, py / 4, size / 4, 3) * 0.25;
      h[py * size + px] = pebble * 0.8 + fine;
      let m = 0, amp = 0.5, freq = 4;
      for (let o = 0; o < 4; o++) {
        m += valueNoise((px / size) * freq, (py / size) * freq, freq, 10 + o) * amp;
        amp *= 0.5; freq *= 2;
      }
      mott[py * size + px] = m;
    }
  }

  const mk = () => {
    const c = document.createElement('canvas');
    c.width = c.height = size;
    return c;
  };
  const nC = mk(), rC = mk(), mC = mk();
  const nCtx = nC.getContext('2d'), rCtx = rC.getContext('2d'), mCtx = mC.getContext('2d');
  const nImg = nCtx.createImageData(size, size), rImg = rCtx.createImageData(size, size), mImg = mCtx.createImageData(size, size);
  const at = (x, y) => h[((y + size) % size) * size + ((x + size) % size)];
  const strength = 2.4;
  for (let y = 0; y < size; y++) {
    for (let x = 0; x < size; x++) {
      const i = (y * size + x) * 4;
      const dx = (at(x + 1, y) - at(x - 1, y)) * strength;
      const dy = (at(x, y + 1) - at(x, y - 1)) * strength;
      const l = Math.hypot(dx, dy, 1);
      nImg.data[i] = ((-dx / l) * 0.5 + 0.5) * 255;
      nImg.data[i + 1] = ((dy / l) * 0.5 + 0.5) * 255;
      nImg.data[i + 2] = ((1 / l) * 0.5 + 0.5) * 255;
      nImg.data[i + 3] = 255;
      const r = (0.55 + (1 - at(x, y)) * 0.45) * 255;
      rImg.data[i] = rImg.data[i + 1] = rImg.data[i + 2] = r;
      rImg.data[i + 3] = 255;
      const m = mott[y * size + x];
      const v = (0.84 + m * 0.2 + at(x, y) * 0.05) * 255;
      mImg.data[i] = mImg.data[i + 1] = mImg.data[i + 2] = Math.min(255, v);
      mImg.data[i + 3] = 255;
    }
  }
  nCtx.putImageData(nImg, 0, 0);
  rCtx.putImageData(rImg, 0, 0);
  mCtx.putImageData(mImg, 0, 0);

  const tex = (c, srgb) => {
    const t = new THREE.CanvasTexture(c);
    t.wrapS = t.wrapT = THREE.RepeatWrapping;
    t.anisotropy = 8;
    if (srgb) t.colorSpace = THREE.SRGBColorSpace;
    return t;
  };
  return { normal: tex(nC), rough: tex(rC), map: tex(mC, true) };
}

function makePlaidTexture() {
  const c = document.createElement('canvas');
  c.width = c.height = 256;
  const g = c.getContext('2d');
  g.fillStyle = '#3a302d';
  g.fillRect(0, 0, 256, 256);
  const band = (pos, w, color, alpha) => {
    g.globalAlpha = alpha;
    g.fillStyle = color;
    g.fillRect(pos, 0, w, 256);
    g.fillRect(0, pos, 256, w);
  };
  band(20, 46, '#5d4d47', 0.55);
  band(150, 46, '#5d4d47', 0.55);
  band(96, 10, '#1e1716', 0.8);
  band(110, 3, '#9b7f65', 0.7);
  band(228, 3, '#9b7f65', 0.7);
  band(60, 2, '#1e1716', 0.8);
  g.globalAlpha = 1;
  const t = new THREE.CanvasTexture(c);
  t.wrapS = t.wrapT = THREE.RepeatWrapping;
  t.repeat.set(3, 3);
  t.colorSpace = THREE.SRGBColorSpace;
  return t;
}

function makeRadialTexture(stops) {
  const c = document.createElement('canvas');
  c.width = c.height = 256;
  const g = c.getContext('2d');
  const grd = g.createRadialGradient(128, 128, 0, 128, 128, 128);
  stops.forEach(([o, col]) => grd.addColorStop(o, col));
  g.fillStyle = grd;
  g.fillRect(0, 0, 256, 256);
  const t = new THREE.CanvasTexture(c);
  t.colorSpace = THREE.SRGBColorSpace;
  return t;
}

/* ------------------------------ geometry ------------------------------ */

const PROFILE = new THREE.SplineCurve(
  [
    [0.585, -1.05], [0.59, -0.85], [0.605, -0.55], [0.64, -0.2], [0.7, 0.15],
    [0.735, 0.45], [0.73, 0.66], [0.655, 0.84], [0.47, 0.975], [0.3, 1.04], [0.255, 1.075],
  ].map(([r, y]) => new THREE.Vector2(r, y)),
);
const PROFILE_LUT = PROFILE.getPoints(1024);
function prof(t) {
  const f = clamp01(t) * 1024;
  const i = Math.min(1023, Math.floor(f));
  const a = PROFILE_LUT[i], b = PROFILE_LUT[i + 1];
  const k = f - i;
  return { r: lerp(a.x, b.x, k), y: lerp(a.y, b.y, k) };
}

function surf(phi, t, off = 0, out = new THREE.Vector3()) {
  const p = prof(t);
  const r = p.r + off;
  return out.set(Math.sin(phi) * r, p.y, Math.cos(phi) * r * DEPTH);
}

function surfNormal(phi, t, off = 0) {
  const e = 0.002;
  const a = surf(phi + e, t, off).sub(surf(phi - e, t, off));
  const b = surf(phi, Math.min(1, t + e), off).sub(surf(phi, Math.max(0, t - e), off));
  return a.cross(b).normalize();
}

const gapFn = (t) => 0.03 + Math.pow(smooth(V_START, 1.0, t), 1.15) * 0.5;

/** Grid surface on the torso between phiA(t)..phiB(t) and t0..t1. */
function panelGeo(phiA, phiB, t0, t1, segU, segV, off, uvScale = 3.2) {
  const fa = typeof phiA === 'function' ? phiA : () => phiA;
  const fb = typeof phiB === 'function' ? phiB : () => phiB;
  const pos = [], uv = [], idx = [];
  const v = new THREE.Vector3();
  for (let j = 0; j <= segV; j++) {
    const t = lerp(t0, t1, j / segV);
    const a = fa(t), b = fb(t);
    const y = prof(t).y;
    for (let i = 0; i <= segU; i++) {
      const phi = lerp(a, b, i / segU);
      surf(phi, t, off, v);
      pos.push(v.x, v.y, v.z);
      uv.push(phi * 0.72 * uvScale, y * uvScale);
    }
  }
  const row = segU + 1;
  for (let j = 0; j < segV; j++)
    for (let i = 0; i < segU; i++) {
      const a = j * row + i, b = a + 1, d = a + row, c = d + 1;
      idx.push(a, b, d, b, c, d);
    }
  const g = new THREE.BufferGeometry();
  g.setAttribute('position', new THREE.Float32BufferAttribute(pos, 3));
  g.setAttribute('uv', new THREE.Float32BufferAttribute(uv, 2));
  g.setIndex(idx);
  g.computeVertexNormals();
  return g;
}

/** Ring band (e.g. stand collar) described by rows of {r, y}. */
function bandGeo(phiA, phiB, rows, segU) {
  const pos = [], uv = [], idx = [];
  rows.forEach(({ r, y }, j) => {
    for (let i = 0; i <= segU; i++) {
      const phi = lerp(phiA, phiB, i / segU);
      pos.push(Math.sin(phi) * r, y, Math.cos(phi) * r * DEPTH);
      uv.push(phi * 0.9, j * 0.18);
    }
  });
  const row = segU + 1;
  for (let j = 0; j < rows.length - 1; j++)
    for (let i = 0; i < segU; i++) {
      const a = j * row + i, b = a + 1, d = a + row, c = d + 1;
      idx.push(a, b, d, b, c, d);
    }
  const g = new THREE.BufferGeometry();
  g.setAttribute('position', new THREE.Float32BufferAttribute(pos, 3));
  g.setAttribute('uv', new THREE.Float32BufferAttribute(uv, 2));
  g.setIndex(idx);
  g.computeVertexNormals();
  return g;
}

/** Sweep a variable-radius tube along a curve (used for sleeves). */
function makeSweep(curve, radiusFn, segS, segT) {
  const frames = curve.computeFrenetFrames(segS, false);
  const P = [];
  for (let j = 0; j <= segS; j++) P.push(curve.getPointAt(j / segS));
  const pos = [], uv = [], idx = [];
  const tmp = new THREE.Vector3();
  for (let j = 0; j <= segS; j++) {
    const s = j / segS;
    for (let i = 0; i <= segT; i++) {
      const th = (i / segT) * TAU;
      const r = radiusFn(s, th, j);
      tmp.copy(frames.normals[j]).multiplyScalar(Math.cos(th)).addScaledVector(frames.binormals[j], Math.sin(th));
      pos.push(P[j].x + tmp.x * r, P[j].y + tmp.y * r, P[j].z + tmp.z * r);
      uv.push((th / TAU) * 2.4, s * 4.4);
    }
  }
  const row = segT + 1;
  for (let j = 0; j < segS; j++)
    for (let i = 0; i < segT; i++) {
      const a = j * row + i, b = a + 1, d = a + row, c = d + 1;
      idx.push(a, b, d, b, c, d);
    }
  const g = new THREE.BufferGeometry();
  g.setAttribute('position', new THREE.Float32BufferAttribute(pos, 3));
  g.setAttribute('uv', new THREE.Float32BufferAttribute(uv, 2));
  g.setIndex(idx);
  g.computeVertexNormals();

  const at = (s, th, off = 0) => {
    const j = Math.round(clamp01(s) * segS);
    tmp.copy(frames.normals[j]).multiplyScalar(Math.cos(th)).addScaledVector(frames.binormals[j], Math.sin(th));
    return P[j].clone().addScaledVector(tmp, radiusFn(s, th, j) + off);
  };
  const normalAt = (s, th) => {
    const j = Math.round(clamp01(s) * segS);
    return frames.normals[j].clone().multiplyScalar(Math.cos(th)).addScaledVector(frames.binormals[j], Math.sin(th));
  };
  const thetaToward = (s, dir) => {
    const j = Math.round(clamp01(s) * segS);
    return Math.atan2(dir.dot(frames.binormals[j]), dir.dot(frames.normals[j]));
  };
  return { geo: g, at, normalAt, thetaToward };
}

/* ------------------------------ stitching ------------------------------ */

const stitchVert = /* glsl */ `
  varying vec2 vUv;
  varying vec3 vN;
  void main(){
    vUv = uv;
    vN = normalize(normalMatrix * normal);
    gl_Position = projectionMatrix * modelViewMatrix * vec4(position, 1.0);
  }`;
const stitchFrag = /* glsl */ `
  uniform float uProgress;
  uniform float uDash;
  uniform float uHead;
  uniform float uPulse;
  uniform vec3 uColor;
  varying vec2 vUv;
  varying vec3 vN;
  void main(){
    if (vUv.x > uProgress) discard;
    float d = fract(vUv.x * uDash);
    if (d > 0.62) discard;
    float light = 0.45 + 0.55 * max(dot(vN, normalize(vec3(0.4, 0.8, 0.6))), 0.0);
    vec3 col = uColor * light;
    float head = smoothstep(0.08, 0.0, uProgress - vUv.x) * uHead;
    col += vec3(1.0, 0.72, 0.35) * head * 2.6;
    col += vec3(1.0, 0.8, 0.5) * uPulse * 0.9;
    gl_FragColor = vec4(col, 1.0);
    #include <tonemapping_fragment>
    #include <colorspace_fragment>
  }`;

/* ------------------------------ scene ------------------------------ */

// [anatomy progress, turntable angle]: hide -> stitch (right sleeve) -> zipper -> lining
const ANAT_KEYS = [[0, 0], [0.15, -0.35], [0.3, -0.85], [0.45, -0.85], [0.55, 0], [0.7, 0], [0.8, 0.95], [1, 0.95]];
function keyframes(keys, x) {
  for (let i = 1; i < keys.length; i++) {
    if (x <= keys[i][0]) return lerp(keys[i - 1][1], keys[i][1], smooth(keys[i - 1][0], keys[i][0], x));
  }
  return keys[keys.length - 1][1];
}

export function createJacketScene(canvas, { onFrame } = {}) {
  const renderer = new THREE.WebGLRenderer({ canvas, antialias: true, alpha: true, powerPreference: 'high-performance' });
  renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
  renderer.toneMapping = THREE.ACESFilmicToneMapping;
  renderer.toneMappingExposure = 1.05;
  renderer.setClearColor(0x000000, 0);

  const scene = new THREE.Scene();
  const camera = new THREE.PerspectiveCamera(32, 1, 0.1, 100);
  camera.position.set(0, 0.15, 7.3);

  const pmrem = new THREE.PMREMGenerator(renderer);
  scene.environment = pmrem.fromScene(new RoomEnvironment(), 0.04).texture;
  scene.environmentIntensity = 0.55;

  // Lights: warm key, cool fill, strong warm rim for leather highlights
  const key = new THREE.DirectionalLight(0xffe2c0, 2.6);
  key.position.set(-3, 4, 5);
  const rim = new THREE.DirectionalLight(0xffb070, 3.2);
  rim.position.set(4, 2.5, -4);
  const rim2 = new THREE.DirectionalLight(0xc8d8ff, 1.2);
  rim2.position.set(-4, 1, -3);
  const fill = new THREE.HemisphereLight(0xfff1e0, 0x1a0f08, 0.35);
  const sweep = new THREE.PointLight(0xffc98a, 0, 6, 1.6);
  sweep.position.set(-2.5, 1, 2.5);
  scene.add(key, rim, rim2, fill, sweep);

  /* materials */
  const tex = makeLeatherTextures();
  const leather = new THREE.MeshPhysicalMaterial({
    color: LEATHER_COLORS.cognac.base,
    map: tex.map,
    normalMap: tex.normal,
    normalScale: new THREE.Vector2(0.28, 0.28),
    roughnessMap: tex.rough,
    roughness: 0.62,
    clearcoat: 0.55,
    clearcoatRoughness: 0.3,
    sheen: 0.35,
    sheenRoughness: 0.6,
    sheenColor: new THREE.Color('#ffd7b0'),
  });
  const lining = new THREE.MeshStandardMaterial({ map: makePlaidTexture(), roughness: 0.85, side: THREE.BackSide });
  const metal = new THREE.MeshStandardMaterial({ color: '#cfc9c0', metalness: 1, roughness: 0.22, envMapIntensity: 1.4 });
  const tape = new THREE.MeshStandardMaterial({ color: '#15110f', roughness: 0.9 });
  const linen = new THREE.MeshStandardMaterial({ color: '#d9cdb9', roughness: 0.92 });
  const wood = new THREE.MeshStandardMaterial({ color: '#5a3a22', roughness: 0.45, metalness: 0.1 });
  const darkMetal = new THREE.MeshStandardMaterial({ color: '#2a2522', metalness: 0.9, roughness: 0.35 });

  const stitchMats = [];
  const threadColor = new THREE.Color(LEATHER_COLORS.cognac.thread);
  function stitchMaterial(length) {
    const m = new THREE.ShaderMaterial({
      vertexShader: stitchVert,
      fragmentShader: stitchFrag,
      uniforms: {
        uProgress: { value: 0 },
        uDash: { value: Math.max(4, length / 0.028) },
        uHead: { value: 1 },
        uPulse: { value: 0 },
        uColor: { value: threadColor },
      },
      toneMapped: true,
    });
    stitchMats.push(m);
    return m;
  }
  function stitch(points, parent, radius = 0.0042) {
    const curve = new THREE.CatmullRomCurve3(points);
    const len = curve.getLength();
    const geo = new THREE.TubeGeometry(curve, Math.max(24, Math.ceil(len * 90)), radius, 4, false);
    const mesh = new THREE.Mesh(geo, stitchMaterial(len));
    parent.add(mesh);
    return mesh;
  }
  const sampleSurf = (fn, n = 90) => Array.from({ length: n + 1 }, (_, i) => fn(i / n));
  // double row of stitches (like a real topstitched seam)
  function seam(fn, parent, spread = 0.011) {
    stitch(sampleSurf((u) => fn(u, -spread)), parent);
    stitch(sampleSurf((u) => fn(u, spread)), parent);
  }

  function piping(points, parent, r = 0.011, mat = leather) {
    const curve = new THREE.CatmullRomCurve3(points);
    const mesh = new THREE.Mesh(new THREE.TubeGeometry(curve, 120, r, 8, false), mat);
    parent.add(mesh);
    return mesh;
  }

  /* zipper built along any function u -> {p, n, tan} */
  const toothGeo = new THREE.BoxGeometry(0.02, 0.0075, 0.009);
  function zipper(frame, count, { width = 0.05, withTape = true, parent, pull = true, double = true } = {}) {
    const g = new THREE.Group();
    const teeth = new THREE.InstancedMesh(toothGeo, metal, count);
    const m = new THREE.Matrix4();
    const across = new THREE.Vector3();
    const tapeL = [], tapeR = [];
    for (let i = 0; i < count; i++) {
      const u = i / (count - 1);
      const { p, n, tan } = frame(u);
      across.crossVectors(tan, n).normalize();
      const side = double ? (i % 2 ? 1 : -1) * 0.0045 : 0;
      m.makeBasis(across, tan, n).setPosition(p.clone().addScaledVector(across, side).addScaledVector(n, 0.004));
      teeth.setMatrixAt(i, m);
    }
    g.add(teeth);
    if (withTape) {
      const pos = [], idx = [];
      const N = 60;
      for (let i = 0; i <= N; i++) {
        const { p, n, tan } = frame(i / N);
        across.crossVectors(tan, n).normalize();
        const a = p.clone().addScaledVector(across, -width / 2).addScaledVector(n, 0.001);
        const b = p.clone().addScaledVector(across, width / 2).addScaledVector(n, 0.001);
        pos.push(a.x, a.y, a.z, b.x, b.y, b.z);
        tapeL.push(a); tapeR.push(b);
        if (i < N) idx.push(i * 2, i * 2 + 1, i * 2 + 2, i * 2 + 1, i * 2 + 3, i * 2 + 2);
      }
      const tg = new THREE.BufferGeometry();
      tg.setAttribute('position', new THREE.Float32BufferAttribute(pos, 3));
      tg.setIndex(idx);
      tg.computeVertexNormals();
      const tm = new THREE.Mesh(tg, tape);
      tm.material.side = THREE.DoubleSide;
      g.add(tm);
    }
    let slider = null;
    if (pull) {
      slider = new THREE.Group();
      const body = new THREE.Mesh(new THREE.BoxGeometry(0.03, 0.042, 0.016), metal);
      const tab = new THREE.Mesh(new THREE.BoxGeometry(0.02, 0.07, 0.005), metal);
      tab.position.set(0, -0.045, 0.012);
      tab.rotation.x = 0.35;
      const ring = new THREE.Mesh(new THREE.TorusGeometry(0.007, 0.0022, 6, 16), metal);
      ring.position.set(0, -0.085, 0.024);
      slider.add(body, tab, ring);
      g.add(slider);
    }
    const setProgress = (pr) => {
      teeth.count = Math.max(0, Math.round(pr * count));
      if (slider) {
        const { p, n, tan } = frame(clamp01(pr));
        across.crossVectors(tan, n).normalize();
        m.makeBasis(across, tan, n);
        slider.quaternion.setFromRotationMatrix(m);
        slider.position.copy(p).addScaledVector(n, 0.012);
      }
    };
    setProgress(1);
    (parent || scene).add(g);
    return { group: g, teeth, slider, setProgress };
  }
  const surfFrame = (phiFn, tFn, off) => (u) => {
    const phi = phiFn(u), t = tFn(u);
    const p = surf(phi, t, off);
    const n = surfNormal(phi, t, off);
    const e = 0.01;
    const p2 = surf(phiFn(Math.min(1, u + e)), tFn(Math.min(1, u + e)), off);
    const p1 = surf(phiFn(Math.max(0, u - e)), tFn(Math.max(0, u - e)), off);
    const tan = p2.sub(p1).normalize();
    return { p, n, tan };
  };

  /* ----------------------------- rig ----------------------------- */
  const root = new THREE.Group(); // layout position/scale
  const turntable = new THREE.Group(); // user rotation
  root.add(turntable);
  scene.add(root);

  // Pedestal + glow + contact shadow
  const pedestal = new THREE.Group();
  const ped = new THREE.Mesh(
    new THREE.CylinderGeometry(0.78, 0.84, 0.1, 96),
    new THREE.MeshPhysicalMaterial({ color: '#141010', roughness: 0.35, metalness: 0.3, clearcoat: 1, clearcoatRoughness: 0.15 }),
  );
  ped.position.y = -1.78;
  const ringGlow = new THREE.Mesh(new THREE.TorusGeometry(0.785, 0.007, 8, 160), new THREE.MeshBasicMaterial({ color: '#e7b77a', transparent: true }));
  ringGlow.rotation.x = Math.PI / 2;
  ringGlow.position.y = -1.728;
  const halo = new THREE.Mesh(
    new THREE.PlaneGeometry(5.5, 5.5),
    new THREE.MeshBasicMaterial({
      map: makeRadialTexture([[0, 'rgba(255,170,90,0.45)'], [0.35, 'rgba(200,110,50,0.14)'], [1, 'rgba(0,0,0,0)']]),
      transparent: true, depthWrite: false, blending: THREE.AdditiveBlending,
    }),
  );
  halo.rotation.x = -Math.PI / 2;
  halo.position.y = -1.84;
  const shadow = new THREE.Mesh(
    new THREE.PlaneGeometry(1.5, 1.5),
    new THREE.MeshBasicMaterial({ map: makeRadialTexture([[0, 'rgba(0,0,0,0.75)'], [1, 'rgba(0,0,0,0)']]), transparent: true, depthWrite: false }),
  );
  shadow.rotation.x = -Math.PI / 2;
  shadow.position.y = -1.726;
  pedestal.add(ped, ringGlow, halo, shadow);
  turntable.add(pedestal);

  // Dress form (mannequin)
  const form = new THREE.Group();
  const torso = new THREE.Mesh(panelGeo(0, TAU, 0, 1, 96, 70, 0), linen);
  const neck = new THREE.Mesh(new THREE.CylinderGeometry(0.16, 0.19, 0.32, 48), linen);
  neck.scale.z = 0.85;
  neck.position.y = 1.2;
  const cap = new THREE.Mesh(new THREE.CylinderGeometry(0.17, 0.16, 0.05, 48), wood);
  cap.scale.z = 0.85;
  cap.position.y = 1.38;
  const knob = new THREE.Mesh(new THREE.SphereGeometry(0.05, 24, 16), wood);
  knob.position.y = 1.44;
  const bottom = new THREE.Mesh(new THREE.CircleGeometry(0.585, 64), wood);
  bottom.rotation.x = Math.PI / 2;
  bottom.scale.y = DEPTH;
  bottom.position.y = -1.05;
  const pole = new THREE.Mesh(new THREE.CylinderGeometry(0.03, 0.03, 0.7, 16), darkMetal);
  pole.position.y = -1.39;
  form.add(torso, neck, cap, knob, bottom, pole);
  turntable.add(form);

  /* ----------------------------- jacket parts ----------------------------- */
  const parts = [];
  function part(name, fly, explode) {
    const outer = new THREE.Group();
    const inner = new THREE.Group();
    outer.add(inner);
    turntable.add(outer);
    const p = {
      name, outer, inner, state: { fly: 0 },
      fly: { pos: new THREE.Vector3(...fly.pos), rot: new THREE.Euler(...(fly.rot || [0, 0, 0])), scale: fly.scale ?? 0.7 },
      explode: new THREE.Vector3(...explode),
    };
    parts.push(p);
    return p;
  }
  const addShell = (geo, parent) => {
    const a = new THREE.Mesh(geo, leather);
    const b = new THREE.Mesh(geo, lining);
    parent.add(a, b);
    return a;
  };

  // Front panels (mirrored via sign)
  function buildFront(sign, p) {
    const phiA = sign > 0 ? (t) => gapFn(t) : -SIDE;
    const phiB = sign > 0 ? SIDE : (t) => -gapFn(t);
    addShell(panelGeo(phiA, phiB, HEM, 1, 60, 90, OFF), p.inner);
    const P = (phi, t, o = 0.008) => surf(phi * sign, t, OFF + o);
    // front edge (follows neckline)
    seam((u, s) => P(gapFn(lerp(HEM, 0.995, u)) + 0.04 + s * 1.4, lerp(HEM, 0.995, u)), p.inner, 0.007);
    // side seam
    stitch(sampleSurf((u) => P(SIDE - 0.025, lerp(HEM, 0.99, u))), p.inner);
    // princess panel seam
    seam((u, s) => P(0.78 + s, lerp(HEM, 0.73, u)), p.inner);
    // chest yoke
    seam((u, s) => P(lerp(gapFn(0.745) + 0.05, SIDE - 0.03, u), 0.745 + u * 0.05 + s), p.inner);
    // piping on the open edge to fake leather thickness
    piping(sampleSurf((u) => P(gapFn(lerp(HEM, 1, u)) + 0.004, lerp(HEM, 1, u), -0.002), 80), p.inner, 0.0085);
    // open neckline zipper row
    zipper(
      surfFrame((u) => sign * (gapFn(lerp(V_START, 0.985, u)) + 0.012), (u) => lerp(V_START, 0.985, u), OFF + 0.002),
      34, { width: 0.022, parent: p.inner, pull: false, double: false },
    );
    // slanted chest zip pocket
    zipper(
      surfFrame((u) => sign * lerp(0.28, 0.7, u), (u) => lerp(0.585, 0.64, u), OFF + 0.004),
      36, { width: 0.022, parent: p.inner, pull: true, double: true },
    ).slider.visible = true;
    // vertical hand-warmer zip pocket
    zipper(
      surfFrame((u) => sign * lerp(0.98, 0.94, u), (u) => lerp(0.17, 0.38, u), OFF + 0.004),
      34, { width: 0.022, parent: p.inner },
    );
    // collar snap
    const snapT = 0.93;
    const snap = new THREE.Mesh(new THREE.CylinderGeometry(0.022, 0.024, 0.012, 24), metal);
    const sp = P(gapFn(snapT) + 0.12, snapT, 0.004);
    snap.position.copy(sp);
    snap.quaternion.setFromUnitVectors(new THREE.Vector3(0, 1, 0), surfNormal(sign * (gapFn(snapT) + 0.12), snapT, OFF));
    p.inner.add(snap);
    return p;
  }
  const frontR = buildFront(1, part('frontR', { pos: [2.6, 0.4, 1.6], rot: [0.2, -1.1, 0.3] }, [0.5, 0.05, 0.75]));
  const frontL = buildFront(-1, part('frontL', { pos: [-2.6, 0.4, 1.6], rot: [0.2, 1.1, -0.3] }, [-0.5, 0.05, 0.75]));

  // Back panel
  const back = part('back', { pos: [0, 0.6, -3], rot: [0.6, 0, 0] }, [0, 0.05, -0.85]);
  addShell(panelGeo(SIDE, TAU - SIDE, HEM, 1, 70, 90, OFF), back.inner);
  {
    const P = (phi, t) => surf(phi, t, OFF + 0.008);
    stitch(sampleSurf((u) => P(Math.PI, lerp(HEM, 0.8, u))), back.inner);
    seam((u, s) => P(lerp(SIDE + 0.03, TAU - SIDE - 0.03, u), 0.8 + s), back.inner);
    seam((u, s) => P(lerp(SIDE + 0.03, TAU - SIDE - 0.03, u), HEM + 0.016 + s * 0.6), back.inner, 0.006);
    stitch(sampleSurf((u) => P(SIDE + 0.025, lerp(HEM, 0.99, u))), back.inner);
    stitch(sampleSurf((u) => P(TAU - SIDE - 0.025, lerp(HEM, 0.99, u))), back.inner);
  }

  // Waistband
  const hem = part('hem', { pos: [0, -1.2, 0.4], scale: 1.25 }, [0, -0.3, 0.45]);
  addShell(panelGeo(gapFn(0), TAU - gapFn(0), 0, HEM + 0.004, 150, 8, OFF + 0.012), hem.inner);
  seam((u, s) => surf(lerp(gapFn(0) + 0.02, TAU - gapFn(0) - 0.02, u), HEM - 0.014 + s * 0.4, OFF + 0.02), hem.inner, 0.008);
  stitch(sampleSurf((u) => surf(lerp(gapFn(0) + 0.02, TAU - gapFn(0) - 0.02, u), 0.012, OFF + 0.02), 160), hem.inner);
  // side adjuster tabs with buttons
  [1, -1].forEach((sgn) => {
    const phi = sgn * (SIDE - 0.12);
    const btn = new THREE.Mesh(new THREE.CylinderGeometry(0.02, 0.022, 0.012, 20), metal);
    btn.position.copy(surf(phi, HEM * 0.5, OFF + 0.026));
    btn.quaternion.setFromUnitVectors(new THREE.Vector3(0, 1, 0), surfNormal(phi, HEM * 0.5, OFF));
    hem.inner.add(btn);
  });

  // Stand collar
  const collar = part('collar', { pos: [0, 1.6, 0.3], rot: [-0.9, 0, 0] }, [0, 0.6, 0.1]);
  {
    const g0 = gapFn(1) - 0.02;
    const top = prof(1);
    const rows = [
      { r: top.r + OFF - 0.004, y: top.y - 0.02 },
      { r: top.r + OFF + 0.004, y: top.y + 0.04 },
      { r: top.r + OFF + 0.002, y: top.y + 0.1 },
      { r: top.r + OFF - 0.008, y: top.y + 0.145 },
    ];
    addShell(bandGeo(g0, TAU - g0, rows, 90), collar.inner);
    const ring = (y, r) => sampleSurf((u) => {
      const phi = lerp(g0 + 0.03, TAU - g0 - 0.03, u);
      return new THREE.Vector3(Math.sin(phi) * r, y, Math.cos(phi) * r * DEPTH);
    }, 120);
    stitch(ring(top.y + 0.125, top.r + OFF + 0.002), collar.inner);
    stitch(ring(top.y + 0.02, top.r + OFF + 0.008), collar.inner);
    piping(ring(top.y + 0.145, top.r + OFF - 0.008), collar.inner, 0.009);
    [1, -1].forEach((sgn) => {
      const phi = sgn * (g0 + 0.09);
      const r = top.r + OFF + 0.01;
      const snap = new THREE.Mesh(new THREE.CylinderGeometry(0.018, 0.02, 0.012, 20), metal);
      snap.position.set(Math.sin(phi) * r, top.y + 0.075, Math.cos(phi) * r * DEPTH);
      snap.quaternion.setFromUnitVectors(new THREE.Vector3(0, 1, 0), new THREE.Vector3(Math.sin(phi), 0, Math.cos(phi) * DEPTH).normalize());
      collar.inner.add(snap);
    });
  }

  // Sleeves (right built once, left mirrored)
  const sleeveCurve = new THREE.CatmullRomCurve3([
    new THREE.Vector3(0.57, 0.7, 0.0),
    new THREE.Vector3(0.77, 0.48, 0.02),
    new THREE.Vector3(0.9, 0.02, 0.07),
    new THREE.Vector3(0.95, -0.47, 0.14),
    new THREE.Vector3(0.94, -0.95, 0.22),
  ]);
  const RIB0 = 0.13, RIB1 = 0.37, RIBW = 0.03;
  let ribCenter = 0;
  const baseR = (s) => {
    let r = lerp(0.215, 0.168, smooth(0, 0.55, s));
    r = lerp(r, 0.138, smooth(0.5, 0.95, s));
    return r + smooth(0.9, 0.985, s) * 0.007;
  };
  const angDist = (a, b) => Math.abs(Math.atan2(Math.sin(a - b), Math.cos(a - b)));
  const ribAmt = (s, th) => {
    if (s < RIB0 || s > RIB1) return 0;
    const a = smooth(1.5, 0.9, angDist(th, ribCenter));
    return Math.abs(Math.sin(((s - RIB0) / RIBW) * Math.PI)) * 0.013 * a;
  };
  // compute rib centre from a first pass (direction pointing up & outward)
  ribCenter = makeSweep(sleeveCurve, baseR, 8, 8).thetaToward(0.25, new THREE.Vector3(0.75, 0.66, 0.1).normalize());
  const sleeveSweep = makeSweep(sleeveCurve, (s, th) => baseR(s) + ribAmt(s, th), 150, 48);

  function buildSleeve(sign) {
    const p = part(
      sign > 0 ? 'sleeveR' : 'sleeveL',
      { pos: [sign * 2.8, 1.0, 0.2], rot: [0, 0, sign * 1.1] },
      [sign * 0.75, 0.15, 0.05],
    );
    p.inner.scale.x = sign;
    addShell(sleeveSweep.geo, p.inner);
    const S = (s, th, o = 0.006) => sleeveSweep.at(s, th, o);
    const ring = (s, a0 = 0, a1 = TAU, o) => sampleSurf((u) => S(s, lerp(a0, a1, u), o), 90);
    stitch(ring(0.055), p.inner);
    for (let k = 0; k <= (RIB1 - RIB0) / RIBW + 0.01; k++) {
      stitch(ring(RIB0 + k * RIBW, ribCenter - 1.25, ribCenter + 1.25, 0.004), p.inner);
    }
    // under-arm seam
    stitch(sampleSurf((u) => S(lerp(0.06, 0.985, u), ribCenter + Math.PI)), p.inner);
    seam((u, s) => S(0.9 + s, lerp(0, TAU, u)), p.inner, 0.006);
    stitch(ring(0.978), p.inner);
    // cuff zipper
    const zTh = ribCenter + Math.PI * 0.72;
    zipper(
      (u) => {
        const s = lerp(0.985, 0.8, u);
        const pp = S(s, zTh, 0.002);
        const n = sleeveSweep.normalAt(s, zTh);
        const tan = S(Math.max(0, s - 0.01), zTh, 0.002).sub(S(Math.min(1, s + 0.01), zTh, 0.002)).normalize();
        return { p: pp, n, tan };
      },
      30, { width: 0.022, parent: p.inner },
    );
    return p;
  }
  const sleeveR = buildSleeve(1);
  const sleeveL = buildSleeve(-1);

  // Centre front zipper (animated)
  const zipPart = part('zip', { pos: [0, 0, 0], scale: 1 }, [0, 0, 1.15]);
  const mainZip = zipper(
    surfFrame(() => 0, (u) => lerp(0.004, V_START + 0.005, u), OFF + 0.003),
    92, { width: 0.05, parent: zipPart.inner },
  );

  /* dust particles */
  const dustCount = 220;
  const dustGeo = new THREE.BufferGeometry();
  const dp = new Float32Array(dustCount * 3);
  const dSeed = new Float32Array(dustCount);
  for (let i = 0; i < dustCount; i++) {
    dp[i * 3] = (Math.random() - 0.5) * 7;
    dp[i * 3 + 1] = (Math.random() - 0.5) * 5;
    dp[i * 3 + 2] = (Math.random() - 0.5) * 4;
    dSeed[i] = Math.random();
  }
  dustGeo.setAttribute('position', new THREE.BufferAttribute(dp, 3));
  const dust = new THREE.Points(
    dustGeo,
    new THREE.PointsMaterial({
      size: 0.035, map: makeRadialTexture([[0, 'rgba(255,220,170,1)'], [1, 'rgba(255,200,140,0)']]),
      transparent: true, depthWrite: false, blending: THREE.AdditiveBlending, opacity: 0.6,
    }),
  );
  scene.add(dust);

  /* anchors for HTML hotspots */
  const anchors = {};
  const anchor = (key, parent, pos) => {
    const o = new THREE.Object3D();
    o.position.copy(pos);
    parent.add(o);
    anchors[key] = o;
  };
  anchor('leather', frontR.inner, surf(1.05, 0.5, OFF));
  anchor('stitch', sleeveR.inner, sleeveSweep.at(0.24, ribCenter, 0.02));
  anchor('zipper', zipPart.inner, surf(0, 0.35, OFF + 0.01));
  anchor('lining', frontL.inner, surf(-0.75, 0.42, OFF - 0.01));
  anchor('collar', collar.inner, new THREE.Vector3(0.22, prof(1).y + 0.1, 0.12));

  /* ----------------------------- state & animation ----------------------------- */
  const state = {
    heroP: 0,
    anatRot: 0,
    anatP: 0,
    explode: 0,
    pulse: 0,
    formRise: 0,
    zip: 0,
    drag: 0,
    dragVel: 0,
    introSpin: -0.9,
    pointer: new THREE.Vector2(),
    active: true,
    layout: { heroX: 1.25, heroY: 0, heroS: 1, anatX: 0, anatY: 0, anatS: 1, camZ: 7.3 },
  };

  function applyParts() {
    const e = state.explode;
    for (const p of parts) {
      const f = p.state.fly;
      const k = 1 - f;
      p.outer.position.copy(p.fly.pos).multiplyScalar(k).addScaledVector(p.explode, e);
      p.outer.rotation.set(p.fly.rot.x * k, p.fly.rot.y * k, p.fly.rot.z * k);
      p.outer.scale.setScalar(lerp(p.fly.scale, 1, f));
      p.outer.visible = f > 0.001;
    }
    mainZip.setProgress(state.zip);
    form.position.y = lerp(-0.6, 0, state.formRise);
    form.scale.setScalar(lerp(0.85, 1, state.formRise));
    form.visible = state.formRise > 0.001;
    pedestal.scale.setScalar(lerp(0.6, 1, Math.min(1, state.formRise * 1.5)));
    ringGlow.material.opacity = state.formRise;
    stitchMats.forEach((m) => (m.uniforms.uPulse.value = state.pulse));
  }

  let intro = null;
  function playIntro() {
    if (intro) intro.kill();
    parts.forEach((p) => (p.state.fly = 0));
    stitchMats.forEach((m) => { m.uniforms.uProgress.value = 0; m.uniforms.uHead.value = 1; });
    state.zip = 0;
    state.formRise = 0;
    state.introSpin = -0.9;
    const tl = gsap.timeline();
    tl.to(state, { formRise: 1, duration: 1.4, ease: 'power3.out' }, 0);
    tl.to(state, { introSpin: 0.35, duration: 6.5, ease: 'power2.inOut' }, 0);
    const order = [
      ['back', 0.7], ['frontR', 1.0], ['frontL', 1.12], ['hem', 1.35],
      ['sleeveR', 1.55], ['sleeveL', 1.65], ['collar', 1.95], ['zip', 1.45],
    ];
    order.forEach(([n, at]) => {
      const p = parts.find((q) => q.name === n);
      tl.to(p.state, { fly: 1, duration: 1.35, ease: 'expo.out' }, at);
    });
    // stitching: drawn part by part like a sewing machine pass
    const groups = ['back', 'frontR', 'frontL', 'hem', 'sleeveR', 'sleeveL', 'collar'];
    groups.forEach((n, gi) => {
      const p = parts.find((q) => q.name === n);
      const mats = [];
      p.inner.traverse((o) => o.material && o.material.uniforms?.uProgress && mats.push(o.material));
      mats.forEach((m, i) => {
        tl.to(m.uniforms.uProgress, { value: 1, duration: 1.1, ease: 'power1.inOut' }, 2.55 + gi * 0.2 + i * 0.03);
        tl.to(m.uniforms.uHead, { value: 0, duration: 0.6 }, 3.6 + gi * 0.2 + i * 0.03);
      });
    });
    tl.to(state, { zip: 1, duration: 1.5, ease: 'power2.inOut' }, 3.3);
    tl.to(sweep, { intensity: 14, duration: 0.6, ease: 'power2.out' }, 4.6);
    tl.to(sweep.position, { x: 2.5, duration: 1.6, ease: 'power1.inOut' }, 4.6);
    tl.to(sweep, { intensity: 0, duration: 0.8 }, 5.6);
    tl.set(sweep.position, { x: -2.5 });
    intro = tl;
    return tl;
  }

  function setColor(key, instant = false) {
    const c = LEATHER_COLORS[key];
    if (!c) return;
    const to = new THREE.Color(c.base), th = new THREE.Color(c.thread);
    if (instant) {
      leather.color.copy(to);
      threadColor.copy(th);
      return;
    }
    gsap.to(leather.color, { r: to.r, g: to.g, b: to.b, duration: 0.9, ease: 'power2.out' });
    gsap.to(threadColor, { r: th.r, g: th.g, b: th.b, duration: 0.9, ease: 'power2.out' });
    gsap.fromTo(state, { pulse: 0.0 }, { pulse: 0.8, duration: 0.25, yoyo: true, repeat: 1 });
    gsap.fromTo(turntable.rotation, { x: 0 }, { x: 0.05, duration: 0.3, yoyo: true, repeat: 1, ease: 'sine.inOut' });
  }

  /* ----------------------------- interaction ----------------------------- */
  let dragging = false, lastX = 0, startY = 0, horizontal = null;
  canvas.addEventListener('pointerdown', (e) => {
    dragging = true; lastX = e.clientX; startY = e.clientY; horizontal = null;
  });
  window.addEventListener('pointermove', (e) => {
    state.pointer.set((e.clientX / window.innerWidth) * 2 - 1, -(e.clientY / window.innerHeight) * 2 + 1);
    if (!dragging) return;
    const dx = e.clientX - lastX;
    if (horizontal === null && Math.abs(dx) + Math.abs(e.clientY - startY) > 6) horizontal = Math.abs(dx) > Math.abs(e.clientY - startY);
    if (horizontal) {
      state.dragVel = dx * 0.008;
      state.drag += state.dragVel;
      canvas.classList.add('grabbing');
    }
    lastX = e.clientX;
  });
  window.addEventListener('pointerup', () => { dragging = false; canvas.classList.remove('grabbing'); });

  /* ----------------------------- layout ----------------------------- */
  function resize() {
    const w = window.innerWidth, h = window.innerHeight;
    renderer.setSize(w, h, false);
    camera.aspect = w / h;
    const narrow = w / h < 0.95;
    state.layout = narrow
      ? { heroX: 0, heroY: -1.3, heroS: 0.74, anatX: 0, anatY: -0.75, anatS: 0.66, camZ: 9.2 }
      : { heroX: Math.min(1.35, 0.8 * camera.aspect), heroY: 0.12, heroS: 1, anatX: 0.35, anatY: -0.03, anatS: 0.95, camZ: 7.3 };
    camera.updateProjectionMatrix();
  }
  window.addEventListener('resize', resize);
  resize();

  /* ----------------------------- loop ----------------------------- */
  const clock = new THREE.Timer();
  const v = new THREE.Vector3();
  const anchorOut = {};
  function frame(ts) {
    requestAnimationFrame(frame);
    clock.update(ts);
    const dt = Math.min(clock.getDelta(), 0.05);
    const time = clock.getElapsed();
    if (!state.active) return;

    const L = state.layout;
    const h = smooth(0, 1, state.heroP);
    state.explode = smooth(0.08, 0.45, state.anatP) * (1 - smooth(0.88, 1, state.anatP));
    root.position.x = lerp(L.heroX, L.anatX, h);
    root.position.y = lerp(L.heroY, L.anatY, h) + Math.sin(time * 0.8) * 0.02;
    root.scale.setScalar(lerp(L.heroS, L.anatS * (1 - state.explode * 0.12), h));
    camera.position.z = L.camZ;

    if (!dragging) {
      state.dragVel *= 0.93;
      state.drag += state.dragVel;
    }
    const idle = Math.sin(time * 0.35) * 0.28 * (1 - h * 0.7);
    // each anatomy step turns the jacket toward the part being described
    const anatTarget = keyframes(ANAT_KEYS, state.anatP) - state.introSpin * h;
    state.anatRot += (anatTarget - state.anatRot) * (1 - Math.exp(-dt * 4.5));
    turntable.rotation.y = state.introSpin + idle + state.drag + state.anatRot;

    // gentle camera parallax
    camera.position.x += (state.pointer.x * 0.25 - camera.position.x) * 0.04;
    camera.position.y += (0.15 + state.pointer.y * 0.15 - camera.position.y) * 0.04;
    camera.lookAt(0, 0, 0);

    // stitching highlight during the "hand-stitched" step of the anatomy tour
    const stitchStep = smooth(0.26, 0.32, state.anatP) * (1 - smooth(0.46, 0.5, state.anatP));
    state.pulse = Math.max(state.pulse * 0.96, stitchStep * (0.45 + Math.sin(time * 5) * 0.25));
    applyParts();

    // dust
    const arr = dust.geometry.attributes.position.array;
    for (let i = 0; i < dustCount; i++) {
      arr[i * 3 + 1] += dt * (0.05 + dSeed[i] * 0.08);
      arr[i * 3] += Math.sin(time * 0.5 + dSeed[i] * 10) * dt * 0.02;
      if (arr[i * 3 + 1] > 2.6) arr[i * 3 + 1] = -2.4;
    }
    dust.geometry.attributes.position.needsUpdate = true;

    renderer.render(scene, camera);

    if (onFrame) {
      const w = window.innerWidth, hh = window.innerHeight;
      for (const k in anchors) {
        anchors[k].getWorldPosition(v);
        v.project(camera);
        anchorOut[k] = { x: (v.x * 0.5 + 0.5) * w, y: (-v.y * 0.5 + 0.5) * hh, visible: v.z < 1 };
      }
      onFrame({ anchors: anchorOut, explode: state.explode, anatP: state.anatP });
    }
  }
  applyParts();
  requestAnimationFrame(frame);

  return {
    playIntro,
    setColor,
    setHeroProgress: (p) => (state.heroP = p),
    setAnatomyProgress: (p) => (state.anatP = p),
    setActive: (a) => {
      state.active = a;
    },
    spin: (amount = Math.PI * 2) => gsap.to(state, { drag: state.drag + amount, duration: 1.6, ease: 'power3.inOut' }),
  };
}

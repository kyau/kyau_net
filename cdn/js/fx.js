// $KYAULabs: fx.js kyau@aura.kyaulabs 2026/09/14 -0700 Exp $

/**
 * kyau.net — WebGL effects (three.js, vendored)
 *
 * - hero-fx:   "aurora nebula" — a few thousand additive particles in a
 *              slow sine-undulating sheet, gradient teal -> violet -> pink,
 *              with pointer parallax.
 * - prism-net: faithful port of prism.kyaulabs.com's networked-dots
 *              constellation (900 seeded points, 2-nearest-neighbor lines,
 *              additive blending) — minus the prism effect.
 *
 * Both scenes honor prefers-reduced-motion (single static frame), pause
 * while off-screen and degrade silently when WebGL is unavailable.
 */

import * as THREE from "three";

const reducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)");
const DPR = Math.min(window.devicePixelRatio || 1, 2);

/* -------------------------------------------------------------------------
 * Shared helpers
 * ------------------------------------------------------------------------- */

/** Deterministic PRNG — same algorithm + seed as prism.kyaulabs.com/bg.js */
function mulberry32(a) {
	let t = a >>> 0;
	return function () {
		t = (t |= 0) + 1831565813 | 0;
		let r = Math.imul(t ^ (t >>> 15), 1 | t);
		return (((r = (r + Math.imul(r ^ (r >>> 7), 61 | r)) ^ r) ^ (r >>> 14)) >>> 0) / 4294967296;
	};
}

/** Soft round sprite texture for round glowing points. */
function makeSprite() {
	const size = 64;
	const c = document.createElement("canvas");
	c.width = c.height = size;
	const ctx = c.getContext("2d");
	const g = ctx.createRadialGradient(32, 32, 0, 32, 32, 32);
	g.addColorStop(0, "rgba(255,255,255,1)");
	g.addColorStop(0.55, "rgba(255,255,255,1)");
	g.addColorStop(0.75, "rgba(255,255,255,0.28)");
	g.addColorStop(1, "rgba(255,255,255,0)");
	ctx.fillStyle = g;
	ctx.fillRect(0, 0, size, size);
	const tex = new THREE.CanvasTexture(c);
	tex.colorSpace = THREE.SRGBColorSpace;
	return tex;
}

function makeRenderer(canvas) {
	try {
		const r = new THREE.WebGLRenderer({
			canvas,
			alpha: true,
			antialias: true,
			powerPreference: "low-power",
		});
		r.setClearColor(0, 0);
		r.setPixelRatio(DPR);
		return r;
	} catch {
		return null; // no WebGL — the CSS backdrop carries the scene
	}
}

/** Render loop that pauses off-screen and freezes under reduced motion. */
function runScene(canvas, renderer, scene, camera, tick) {
	const render = (t) => {
		tick(t);
		renderer.render(scene, camera);
	};

	const resize = () => {
		const { clientWidth: w, clientHeight: h } = canvas;
		renderer.setSize(w, h, false);
		camera.aspect = w / Math.max(1, h);
		camera.updateProjectionMatrix();
	};
	resize();
	window.addEventListener("resize", resize, { passive: true });

	if (reducedMotion.matches) {
		render(0); // one static frame only
		return;
	}

	let visible = false;
	let raf = 0;
	const loop = (t) => {
		raf = requestAnimationFrame(loop);
		if (visible) {
			render(t * 0.001);
		}
	};
	new IntersectionObserver((entries) => {
		visible = entries[0]?.isIntersecting ?? false;
	}).observe(canvas);
	raf = requestAnimationFrame(loop);
}

/* -------------------------------------------------------------------------
 * Hero — nebula: the original particle sheet as small crisp squares
 * (no sprite map = square points), brand colors, sine undulation.
 * ------------------------------------------------------------------------- */

function initHero() {
	const canvas = document.getElementById("hero-fx");
	if (!canvas) {
		return;
	}
	const renderer = makeRenderer(canvas);
	if (!renderer) {
		return;
	}

	const scene = new THREE.Scene();
	const camera = new THREE.PerspectiveCamera(55, 1, 0.1, 400);
	camera.position.set(0, 6, 46);
	camera.lookAt(0, 0, 0);

	// particle sheet: same distribution, colors and jitter as the nebula
	const COUNT = 6000;
	const rand = mulberry32(20260906);
	const pos = new Float32Array(COUNT * 3);
	const col = new Float32Array(COUNT * 3);
	// brand sweep: blue -> cyan -> violet -> magenta (KYAU night palette)
	const brand = [new THREE.Color("#4963ff"), new THREE.Color("#47eddc"), new THREE.Color("#a835f3"), new THREE.Color("#ff45b1")];
	const mix = new THREE.Color();
	for (let i = 0; i < COUNT; i++) {
		const x = (rand() - 0.5) * 160;
		const z = (rand() - 0.5) * 90;
		pos[3 * i] = x;
		pos[3 * i + 1] = (rand() - 0.5) * 6;
		pos[3 * i + 2] = z;
		const t = Math.min(1, Math.max(0, x / 160 + 0.5)) * (brand.length - 1);
		const seg = Math.min(brand.length - 2, Math.floor(t));
		mix.copy(brand[seg]).lerp(brand[seg + 1], t - seg);
		const j = 0.75 + rand() * 0.25;
		col[3 * i] = mix.r * j;
		col[3 * i + 1] = mix.g * j;
		col[3 * i + 2] = mix.b * j;
	}
	const geo = new THREE.BufferGeometry();
	geo.setAttribute("position", new THREE.BufferAttribute(pos, 3));
	geo.setAttribute("color", new THREE.BufferAttribute(col, 3));
	const base = pos.slice();

	const points = new THREE.Points(
		geo,
		new THREE.PointsMaterial({
			size: 0.45,
			vertexColors: true,
			transparent: true,
			opacity: 0.85,
			depthWrite: false,
			blending: THREE.AdditiveBlending,
			sizeAttenuation: true,
		})
	);
	scene.add(points);

	let px = 0;
	let py = 0;
	window.addEventListener("pointermove", (e) => {
		px = e.clientX / window.innerWidth - 0.5;
		py = e.clientY / window.innerHeight - 0.5;
	}, { passive: true });

	runScene(canvas, renderer, scene, camera, (t) => {
		const p = geo.attributes.position.array;
		for (let i = 0; i < COUNT; i++) {
			const x = base[3 * i];
			const z = base[3 * i + 2];
			p[3 * i + 1] =
				Math.sin(x * 0.08 + t * 0.9) * 2.2 +
				Math.cos(z * 0.11 + t * 0.7) * 1.8 +
				base[3 * i + 1];
		}
		geo.attributes.position.needsUpdate = true;
		points.rotation.y = t * 0.02 + px * 0.12;
		camera.position.y = 6 - py * 3;
		camera.lookAt(0, 0, 0);
	});
}

/* -------------------------------------------------------------------------
 * Prism — networked-dots constellation (port of prism.kyaulabs.com/bg.js)
 * ------------------------------------------------------------------------- */
function initPrism() {
	const canvas = document.getElementById("prism-net");
	if (!canvas) {
		return;
	}
	const renderer = makeRenderer(canvas);
	if (!renderer) {
		return;
	}

	const scene = new THREE.Scene();
	const camera = new THREE.PerspectiveCamera(55, 1, 0.1, 400);
	camera.position.set(0, 0, 62);

	const sprite = makeSprite();
	const m = 900;
	const rand = mulberry32(641705792); // original seed — identical layout
	const pos = new Float32Array(3 * m);
	const group = new Uint8Array(m);
	for (let i = 0; i < m; i++) {
		const r = 26 + 62 * rand();
		const theta = rand() * Math.PI * 2;
		const phi = Math.acos(2 * rand() - 1);
		pos[3 * i] = r * Math.sin(phi) * Math.cos(theta) * 1.55;
		pos[3 * i + 1] = r * Math.cos(phi) * 0.66;
		pos[3 * i + 2] = r * Math.sin(phi) * Math.sin(theta);
		const bucket = rand();
		group[i] = bucket < 0.6 ? 0 : bucket < 0.9 ? 1 : 2;
	}

	// their three-shade telemetry palette (--accent, --accent-2, --accent-3)
	// with the same brightness jitter (mulberry32(97))
	const shades = [new THREE.Color("#22d3ee"), new THREE.Color("#a78bfa"), new THREE.Color("#f472b6")];
	const jitter = mulberry32(97);
	const col = new Float32Array(3 * m);
	for (let i = 0; i < m; i++) {
		const c = shades[group[i]];
		const o = 0.55 + 0.45 * jitter();
		col[3 * i] = c.r * o;
		col[3 * i + 1] = c.g * o;
		col[3 * i + 2] = c.b * o;
	}

	const pointsGeo = new THREE.BufferGeometry();
	pointsGeo.setAttribute("position", new THREE.BufferAttribute(pos, 3));
	pointsGeo.setAttribute("color", new THREE.BufferAttribute(col, 3));
	const points = new THREE.Points(
		pointsGeo,
		new THREE.PointsMaterial({
			size: 0.8,
			map: sprite,
			vertexColors: true,
			transparent: true,
			opacity: 0.95,
			depthWrite: false,
			blending: THREE.AdditiveBlending,
			sizeAttenuation: true,
		})
	);
	scene.add(points);

	// 2-nearest-neighbor links, as two brightness layers like the original
	// (first-neighbor bright, second-neighbor faint)
	const first = [];
	const second = [];
	const seen = new Set();
	for (let n = 0; n < m; n++) {
		const x = pos[3 * n];
		const y = pos[3 * n + 1];
		const z = pos[3 * n + 2];
		let b1 = -1;
		let b2 = -1;
		let d1 = Infinity;
		let d2 = Infinity;
		for (let k = 0; k < m; k++) {
			if (k === n) {
				continue;
			}
			const dx = pos[3 * k] - x;
			const dy = pos[3 * k + 1] - y;
			const dz = pos[3 * k + 2] - z;
			const d = dx * dx + dy * dy + dz * dz;
			if (d < d1) {
				d2 = d1; b2 = b1; d1 = d; b1 = k;
			} else if (d < d2) {
				d2 = d; b2 = k;
			}
		}
		const key1 = n < b1 ? n * m + b1 : b1 * m + n;
		if (!seen.has(key1)) {
			seen.add(key1);
			first.push(n, b1);
		}
		const key2 = n < b2 ? n * m + b2 : b2 * m + n;
		if (!seen.has(key2)) {
			seen.add(key2);
			second.push(n, b2);
		}
	}
	const buildLines = (pairs, color, opacity) => {
		const lp = new Float32Array(3 * pairs.length);
		for (let i = 0; i < pairs.length; i++) {
			const o = 3 * pairs[i];
			lp[3 * i] = pos[o];
			lp[3 * i + 1] = pos[o + 1];
			lp[3 * i + 2] = pos[o + 2];
		}
		const geo = new THREE.BufferGeometry();
		geo.setAttribute("position", new THREE.BufferAttribute(lp, 3));
		return new THREE.LineSegments(
			geo,
			new THREE.LineBasicMaterial({
				color,
				transparent: true,
				opacity,
				depthWrite: false,
				blending: THREE.AdditiveBlending,
			})
		);
	};
	const linesBright = buildLines(first, new THREE.Color("#22d3ee"), 0.34);
	const linesFaint = buildLines(second, new THREE.Color("#a78bfa"), 0.14);
	scene.add(linesBright, linesFaint);

	// pointer parallax, same easing as the original
	const pointer = { x: 0, y: 0 };
	window.addEventListener("pointermove", (e) => {
		pointer.x = (e.clientX / window.innerWidth) * 2 - 1;
		pointer.y = (e.clientY / window.innerHeight) * 2 - 1;
	}, { passive: true });

	runScene(canvas, renderer, scene, camera, (t) => {
		const ry = 0.018 * t;
		const rx = 0.03 * Math.sin(0.05 * t);
		points.rotation.set(rx, ry, 0);
		linesBright.rotation.set(rx, ry, 0);
		linesFaint.rotation.set(rx, ry, 0);
		camera.position.x += 0.04 * (7 * pointer.x - camera.position.x);
		camera.position.y += 0.04 * (-4.5 * pointer.y - camera.position.y);
		camera.lookAt(0, 0, 0);
	});
}

initHero();
initPrism();

// vim: ft=javascript sts=4 sw=4 ts=4 et :

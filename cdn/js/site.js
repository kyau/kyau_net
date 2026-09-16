// $KYAULabs: site.js kyau@aura.kyaulabs 2026/09/14 -0700 Exp $

/**
 * kyau.net — progressive enhancement
 *
 * Everything here is optional: the page is fully navigable without JS.
 * - Tracks the visible snap section and mirrors it to the rail navigation
 *   (`.is-active` + `aria-current` for assistive technology).
 * - Drives the top scroll-progress bar.
 * - Applies a subtle pointer tilt to the viewport cards (skipped when the
 *   user prefers reduced motion).
 */

const reducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)");

/* -------------------------------------------------------------------------
 * Section tracking → nav state
 * ------------------------------------------------------------------------- */
const sections = Array.from(document.querySelectorAll("main section[id]"));
const navLinks = new Map();

document.querySelectorAll('.site-nav a[href^="#"]').forEach((link) => {
	navLinks.set(link.getAttribute("href").slice(1), link);
});

const setActive = (id) => {
	const section = document.getElementById(id);
	if (section) {
		section.classList.add("is-active");
	}
	const link = navLinks.get(id);
	if (link) {
		link.setAttribute("aria-current", "true");
	}
};

const setInactive = (id) => {
	const section = document.getElementById(id);
	if (section) {
		section.classList.remove("is-active");
	}
	const link = navLinks.get(id);
	if (link) {
		link.removeAttribute("aria-current");
	}
};

if ("IntersectionObserver" in window) {
	const observer = new IntersectionObserver(
		(entries) => {
			entries.forEach((entry) => {
				if (entry.isIntersecting) {
					setActive(entry.target.id);
				} else {
					setInactive(entry.target.id);
				}
			});
		},
		{ rootMargin: "-45% 0px -45% 0px", threshold: 0 }
	);
	sections.forEach((section) => observer.observe(section));
} else {
	// Fallback: everything visible, no scroll tracking.
	sections.forEach((section) => section.classList.add("is-active"));
}

/* -------------------------------------------------------------------------
 * Scroll progress bar (decorative, aria-hidden in markup)
 * ------------------------------------------------------------------------- */
const progress = document.querySelector(".scroll-progress");

if (progress) {
	let ticking = false;
	const update = () => {
		ticking = false;
		const max = document.documentElement.scrollHeight - window.innerHeight;
		const ratio = max > 0 ? window.scrollY / max : 0;
		progress.style.transform = `scaleX(${Math.min(1, Math.max(0, ratio))})`;
	};
	window.addEventListener(
		"scroll",
		() => {
			if (!ticking) {
				ticking = true;
				window.requestAnimationFrame(update);
			}
		},
		{ passive: true }
	);
	update();
}

/* -------------------------------------------------------------------------
 * Pointer tilt on viewport cards (skipped for reduced motion / touch)
 * ------------------------------------------------------------------------- */
const finePointer = window.matchMedia("(pointer: fine)");

const enableTilt = () => {
	if (reducedMotion.matches || !finePointer.matches) {
		return;
	}
	document.querySelectorAll(".project__visual").forEach((visual) => {
		const card = visual.querySelector(".viewport");
		if (!card) {
			return;
		}
		visual.addEventListener("pointermove", (event) => {
			const rect = visual.getBoundingClientRect();
			const x = (event.clientX - rect.left) / rect.width - 0.5;
			const y = (event.clientY - rect.top) / rect.height - 0.5;
			card.style.setProperty("--tilt-x", `${(-y * 6).toFixed(2)}deg`);
			card.style.setProperty("--tilt-y", `${(x * 8).toFixed(2)}deg`);
		});
		visual.addEventListener("pointerleave", () => {
			card.style.setProperty("--tilt-x", "0deg");
			card.style.setProperty("--tilt-y", "0deg");
		});
	});
};

enableTilt();

/* -------------------------------------------------------------------------
 * Metric count-up ([data-count] elements, e.g. the VSI verified-specs
 * panel). Static final value without JS; animates from zero when the
 * element scrolls into view and motion is allowed.
 * ------------------------------------------------------------------------- */
const counters = document.querySelectorAll("[data-count]");

if (counters.length && !reducedMotion.matches) {
	const countUp = (el) => {
		const target = parseInt(el.dataset.count, 10);
		if (!Number.isFinite(target)) {
			return;
		}
		const duration = 1400;
		let start = 0;
		const step = (now) => {
			if (!start) {
				start = now;
			}
			const t = Math.min(1, (now - start) / duration);
			el.textContent = String(Math.round(target * (1 - Math.pow(1 - t, 3))));
			if (t < 1) {
				window.requestAnimationFrame(step);
			}
		};
		window.requestAnimationFrame(step);
	};
	if ("IntersectionObserver" in window) {
		const counterObserver = new IntersectionObserver((entries) => {
			entries.forEach((entry) => {
				if (entry.isIntersecting) {
					counterObserver.unobserve(entry.target);
					countUp(entry.target);
				}
			});
		}, { threshold: 0.4 });
		counters.forEach((el) => counterObserver.observe(el));
	} else {
		counters.forEach(countUp);
	}
}

/* -------------------------------------------------------------------------
 * Deck navigation: any scroll input swaps almost immediately to the next
 * section in that direction (wheel / touch swipe / arrow & page keys).
 * A short deckLock cooldown prevents double-firing mid-animation.
 * Disabled under reduced motion (plain native scrolling instead).
 * ------------------------------------------------------------------------- */
const deck = Array.from(document.querySelectorAll("main section[id], .site-footer"));
let deckLock = false;

const deckIndex = () => {
	let best = 0;
	let bestDist = Infinity;
	deck.forEach((el, i) => {
		const d = Math.abs(el.getBoundingClientRect().top);
		if (d < bestDist) {
			bestDist = d;
			best = i;
		}
	});
	return best;
};

const deckGo = (dir) => {
	const current = deckIndex();
	const next = Math.min(deck.length - 1, Math.max(0, current + dir));
	if (deckLock || next === current) {
		return;
	}
	deckLock = true;
	deck[next].scrollIntoView({ behavior: "smooth", block: "start" });
	setTimeout(() => {
		deckLock = false;
	}, 950);
};

if (deck.length && !reducedMotion.matches) {
	window.addEventListener('wheel', (e) => {
		if (e.ctrlKey || Math.abs(e.deltaY) < 6) {
			return; // pinch-zoom / inertial noise passes through
		}
		e.preventDefault();
		deckGo(e.deltaY > 0 ? 1 : -1);
	}, { passive: false });

	let touchY = null;
	window.addEventListener('touchstart', (e) => {
		touchY = e.touches[0].clientY;
	}, { passive: true });
	window.addEventListener('touchend', (e) => {
		if (touchY === null) {
			return;
		}
		const dy = touchY - e.changedTouches[0].clientY;
		touchY = null;
		if (Math.abs(dy) > 48) {
			deckGo(dy > 0 ? 1 : -1);
		}
	}, { passive: true });

	window.addEventListener('keydown', (e) => {
		if (e.target instanceof Element && e.target.closest("a, button, input, textarea")) {
			return; // don't steal keys from interactive elements
		}
		const dir = { ArrowDown: 1, PageDown: 1, " ": 1, ArrowUp: -1, PageUp: -1 }[e.key];
		if (dir !== undefined) {
			e.preventDefault();
			deckGo(dir);
		} else if (e.key === "Home") {
			e.preventDefault();
			deck[0].scrollIntoView({ behavior: "smooth" });
		} else if (e.key === "End") {
			e.preventDefault();
			deck[deck.length - 1].scrollIntoView({ behavior: "smooth" });
		}
	});
}

/* -------------------------------------------------------------------------
 * Header brand swap: icon in the hero, full KYAU logo past it
 * ------------------------------------------------------------------------- */
const siteHeader = document.querySelector(".site-header");
const heroSection = document.getElementById("top");

if (siteHeader && heroSection && "IntersectionObserver" in window) {
	new IntersectionObserver((entries) => {
		siteHeader.classList.toggle("site-header--scrolled", !entries[0].isIntersecting);
	}, { threshold: 0.15 }).observe(heroSection);
}

/* -------------------------------------------------------------------------
 * KYAU Labs — floating hexagons (exact replica of kyaulabs.com's spawner)
 * ------------------------------------------------------------------------- */
const hexfield = document.querySelector(".hexfield");

if (hexfield && !reducedMotion.matches) {
	const createHexagon = () => {
		const hex = document.createElement("span");
		hex.classList.add("hexagon");
		const scale = +Math.random() + 0.5;
		const left = 100 * Math.random();
		const duration = 20 * Math.random() + 25;
		const delay = 10 * Math.random();
		hex.style.setProperty("--scale", scale);
		hex.style.setProperty("--opacity-hex", Math.round(100 * (0.15 + 0.35 * Math.random())) / 100);
		hex.style.left = left + "%";
		hex.style.animationDuration = duration + "s";
		hex.style.animationDelay = delay + "s";
		hex.style.animationName = "floatUp";
		hexfield.appendChild(hex);
		setTimeout(() => hex.remove(), 1000 * (duration + delay));
	};
	createHexagon();
	setInterval(createHexagon, 1000);
}

// If the user's motion preference changes mid-session, drop any active tilt.
reducedMotion.addEventListener("change", () => {
	if (reducedMotion.matches) {
		document.querySelectorAll(".viewport").forEach((card) => {
			card.style.setProperty("--tilt-x", "0deg");
			card.style.setProperty("--tilt-y", "0deg");
		});
	}
});

// vim: ft=javascript sts=4 sw=4 ts=4 et :

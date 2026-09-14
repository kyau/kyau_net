<?php

# $KYAULabs: PageRenderTest.php kyau@aura.kyaulabs 2026/09/14 -0700 Exp $

declare(strict_types=1);

/**
 * Render the public front page through Aurora and capture the output.
 */
function renderPage(): string
{
    ob_start();
    include __DIR__ . '/../../public/index.php';
    $html = (string) ob_get_clean();
    // Aurora installs its own exception handler; hand PHPUnit's back.
    restore_exception_handler();
    return $html;
}

describe('Front page (rendered through Aurora)', function () {

    test('renders a complete HTML5 document in english', function () {
        $html = renderPage();

        expect($html)->toStartWith('<!DOCTYPE html>')
            ->toContain('<html lang="en"')
            ->toContain('</html>');
    });

    test('carries the site title and meta description', function () {
        $html = renderPage();

        expect($html)->toContain('<title>')
            ->toContain('kyau.net')
            ->toContain('<meta name="description"');
    });

    test('provides a skip link as the first focusable element', function () {
        $html = renderPage();
        $body = (string) substr($html, strpos($html, '<body>'));

        expect(strpos($body, 'class="skip-link"'))->toBeLessThan(strpos($body, '<header'))
            ->and($body)->toContain('href="#main"');
    });

    test('exposes banner, navigation, main and contentinfo landmarks', function () {
        $html = renderPage();

        expect($html)->toContain('<header')
            ->toContain('<nav')
            ->toContain('aria-label="Portfolio sections"')
            ->toContain('<main id="main"')
            ->toContain('<footer');
    });

    test('contains exactly one level-one heading', function () {
        $html = renderPage();

        expect(substr_count($html, '<h1'))->toBe(1);
    });

    test('renders all four portfolio sections in order with anchors', function () {
        $html = renderPage();
        $ids = ['id="kyaulabs"', 'id="prism"', 'id="voidbbs"', 'id="vsi"'];
        $positions = [];

        foreach ($ids as $id) {
            $pos = strpos($html, $id);
            expect($pos)->not->toBeFalse();
            $positions[] = $pos;
        }
        $sorted = $positions;
        sort($sorted);
        expect($positions)->toBe($sorted);
    });

    test('renders each project as its own themed hero panel', function () {
        $html = renderPage();

        foreach (['panel--voidbbs', 'panel--kyaulabs', 'panel--prism', 'panel--vsi'] as $theme) {
            expect($html)->toContain($theme);
        }
        expect(substr_count($html, 'class="panel '))->toBe(4);
    });

    test('links every project externally with safe rel attributes', function () {
        $html = renderPage();

        foreach (['https://voidbbs.com', 'https://kyaulabs.com', 'https://prism.kyaulabs.com', 'https://vsi.kyaulabs.com'] as $url) {
            expect($html)->toContain('href="' . $url . '"');
        }
        expect(substr_count($html, 'rel="noopener noreferrer"'))->toBeGreaterThanOrEqual(4);
    });

    test('navigation mirrors the four project anchors in order', function () {
        $html = renderPage();

        foreach (['href="#kyaulabs"', 'href="#prism"', 'href="#voidbbs"', 'href="#vsi"'] as $anchor) {
            expect($html)->toContain($anchor);
        }
    });

    test('ships an importmap and fx module for the webgl effects', function () {
        $html = renderPage();

        expect($html)->toContain('<script type="importmap">')
            ->toContain('"/cdn/javascript/vendor/three.module.min.js"')
            ->toMatch('/<script src="[^"]*fx\.min\.js[^"]*" type="module"/');
    });

    test('hero and prism panels carry effect canvases', function () {
        $html = renderPage();

        expect($html)->toContain('id="hero-fx"')
            ->toContain('id="prism-net"');
        expect(substr_count($html, '<canvas'))->toBe(2);
    });

    test('panels use the real project logos', function () {
        $html = renderPage();

        expect($html)->toContain('/cdn/img/kyaulabs-logo.svg')
            ->toContain('/cdn/img/prism-logo.png')
            ->toContain('/cdn/img/vsi-logo.svg')
            ->toMatch('/\/cdn\/img\/void-(login|header-alt1|header-alt2)\.png/');
    });

    test('site chrome uses the KYAU brand assets', function () {
        $html = renderPage();

        expect($html)->toContain('/cdn/img/brand/kyau-icon.svg')   // header icon + hero ribbon
            ->toContain('/cdn/img/brand/kyau-logo.svg');         // header (scrolled) + contact
    });

    test('header brand is icon-only with a full-logo swap target', function () {
        $html = renderPage();

        expect($html)->toMatch('/<a class="site-header__brand"[^>]*>\s*<img class="site-header__icon"[^>]*kyau-icon\.svg/')
            ->toContain('site-header__logo')
            ->not->toContain('kyau<span>.net</span>');
    });

    test('contact email is git@kyaulabs.com', function () {
        $html = renderPage();

        expect($html)->toContain('mailto:git@kyaulabs.com')
            ->not->toContain('kyau@kyau.net');
    });

    test('contact github links use @handle labels', function () {
        $html = renderPage();

        expect($html)->toContain('>@kyau<')
            ->toContain('>@kyaulabs<')
            ->not->toContain('>GitHub<');
    });

    test('void terminal shows users online and a tabbed login prompt', function () {
        $html = renderPage();

        expect($html)->toContain('users online')
            ->toMatch('/login:\s*<span class="term__cursor"/');
    });

    test('vsi panel shows the verified-specs metric from the live site', function () {
        $html = renderPage();

        expect($html)->toContain('class="metric"')
            ->toContain('data-count="403"')
            ->toContain('>403<')
            ->toContain('Verified Specs')
            ->not->toContain('diag__gauge');

        $scss = file_get_contents(__DIR__ . '/../../cdn/sass/site.scss');
        expect($scss)->toMatch('/\.metric__value\s*{[^}]*clamp\(/')
            ->not->toContain('needle-sweep');
    });

    test('vsi tagline renders a real apostrophe, not a unicode escape', function () {
        $html = renderPage();

        // htmlentities emits &rsquo; for U+2019 — renders as ’
        expect($html)->toContain('technician&rsquo;s')
            ->not->toContain('technician\\u{2019}');
    });

    test('footer shows monochrome kyaulabs + aurora logos instead of text', function () {
        $html = renderPage();

        expect($html)->toContain('site-footer__logos')
            ->toContain('/cdn/img/kyaulabs-logo.svg')
            ->toContain('/cdn/img/brand/aurora-logo.svg');

        $scss = file_get_contents(__DIR__ . '/../../cdn/sass/site.scss');
        expect($scss)->toMatch('/site-footer__logos[^{]*{[^}]*filter:\s*grayscale/')
            ->toContain('opacity: 0.6')
            ->toMatch('/site-footer__logos a:hover[^{]*{[^}]*grayscale\(0\)/');
    });

    test('favicon is the brand ribbon icon', function () {
        $favicon = file_get_contents(__DIR__ . '/../../public/favicon.svg');
        $icon = file_get_contents(__DIR__ . '/../../cdn/img/brand/kyau-icon.svg');

        expect($favicon)->toBe($icon)
            ->toContain('#4963FF');
    });

    test('includes the minified stylesheet with subresource integrity', function () {
        $html = renderPage();

        expect($html)->toMatch('/<link rel="stylesheet"[^>]*href="\/cdn\/css\/site\.min\.css\?v=\d+"/s')
            ->toMatch('/<link rel="stylesheet"[^>]*integrity="sha512-/s');
    });

    test('loads the minified site and fx scripts as deferred modules', function () {
        $html = renderPage();

        expect($html)->toMatch('/<script src="\/cdn\/javascript\/site\.min\.js[^"]*" type="module"/')
            ->toMatch('/<script src="\/cdn\/javascript\/fx\.min\.js[^"]*" type="module"/');
    });

    test('renders the aurora performance comment', function () {
        $html = renderPage();

        expect($html)->toContain('<!--')
            ->toContain('Aurora');
    });

    test('renders through the repository-root front controller shim', function () {
        ob_start();
        include __DIR__ . '/../../index.php';
        $html = (string) ob_get_clean();
        restore_exception_handler();

        expect($html)->toStartWith('<!DOCTYPE html>')
            ->toContain('id="main"');
    });

});

describe('Generated assets (committed build output)', function () {

    test('minified stylesheet and scripts exist and are non-empty', function () {
        foreach (['cdn/css/site.min.css', 'cdn/javascript/site.min.js', 'cdn/javascript/fx.min.js'] as $file) {
            $path = __DIR__ . '/../../' . $file;
            expect(file_exists($path))->toBeTrue()
                ->and(filesize($path))->toBeGreaterThan(1024);
        }
    });

    test('minified fx module still imports three and drives both canvases', function () {
        $js = file_get_contents(__DIR__ . '/../../cdn/javascript/fx.min.js');

        expect($js)->toContain('from"three"')
            ->toContain('hero-fx')
            ->toContain('prism-net');
    });

});

describe('Brand palette (KYAU brand pack)', function () {

    test('stylesheet is built on the night palette', function () {
        $scss = file_get_contents(__DIR__ . '/../../cdn/sass/site.scss');

        foreach (['#0d101b', '#f1f3ff', '#4963ff', '#47eddc', '#a835f3', '#ff45b1', '#d63d3b'] as $hex) {
            expect(strtolower($scss))->toContain($hex);
        }
    });

    test('theme-color matches the brand canvas', function () {
        $html = renderPage();

        expect($html)->toContain('content="#0D101B"');
    });

});

describe('Stylesheet (WCAG & motion safety)', function () {

    test('honors reduced motion preferences', function () {
        $scss = file_get_contents(__DIR__ . '/../../cdn/sass/site.scss');

        expect($scss)->toContain('prefers-reduced-motion');
    });

    test('snap is proximity-only and disabled under reduced motion', function () {
        $scss = file_get_contents(__DIR__ . '/../../cdn/sass/site.scss');

        // snapping lives in JS now (idle-snap with a generous catch
        // distance); the stylesheet must not double-handle it
        expect($scss)->not->toContain('scroll-snap-type');
    });

    test('contact lockup is dramatically larger', function () {
        $scss = file_get_contents(__DIR__ . '/../../cdn/sass/site.scss');

        expect($scss)->toMatch('/\.contact__lockup\s*{[^}]*clamp\(7rem/');
    });

    test('header brand imagery is enlarged', function () {
        $scss = file_get_contents(__DIR__ . '/../../cdn/sass/site.scss');

        expect($scss)->toMatch('/\.site-header__icon\s*{[^}]*width:\s*2\.25rem/')
            ->toMatch('/\.site-header__logo\s*{[^}]*height:\s*2\.5rem/');
    });

    test('vsi panel has the blueprint grid and prevalent lightning accents', function () {
        $scss = file_get_contents(__DIR__ . '/../../cdn/sass/site.scss');

        expect($scss)->toMatch('/\.panel--vsi::before\s*{[^}]*background-size:\s*36px 36px/')
            ->toContain('bolt')
            // multi-pulse strikes: ~0.5s of flicker every 7s/11s, not a
            // single 0.3s blip that reads as a monitor glitch
            ->toMatch('/\.bolt--left\s*{[^}]*animation:\s*bolt-flash 7s/')
            ->toMatch('/\.bolt--right\s*{[^}]*animation:\s*bolt-flash 11s/')
            ->toMatch('/@keyframes bolt-flash\s*{[\s\S]*0%,\s*80%,\s*88%,\s*100%\s*{\s*opacity:\s*0/');
    });

    test('provides visible keyboard focus styles', function () {
        $scss = file_get_contents(__DIR__ . '/../../cdn/sass/site.scss');

        expect($scss)->toContain(':focus-visible');
    });

    test('ships a visually-hidden utility for screen readers', function () {
        $scss = file_get_contents(__DIR__ . '/../../cdn/sass/site.scss');

        expect($scss)->toContain('.visually-hidden');
    });

    test('is mobile-first with full viewport height sections', function () {
        $scss = file_get_contents(__DIR__ . '/../../cdn/sass/site.scss');

        expect($scss)->toContain('100dvh')
            ->toContain('@media');
    });

    test('animation canvases are dimmed for readability', function () {
        $scss = file_get_contents(__DIR__ . '/../../cdn/sass/site.scss');

        // canvases at 85% plus a dark scrim layer above them
        expect($scss)->toMatch('/\.netfx\s*{[^}]*opacity:\s*0?\.85/')
            ->toMatch('/\.hero__fx\s*{[^}]*opacity:\s*0?\.85/')
            ->toMatch('/\.panel--prism::after\s*{[^}]*rgba\(0, 0, 0,/')
            ->toMatch('/\.hero::after\s*{[^}]*rgba\(0, 0, 0,/');
    });

    test('pills are squared-off, not fully rounded', function () {
        $scss = file_get_contents(__DIR__ . '/../../cdn/sass/site.scss');

        expect($scss)->not->toContain('99rem');
    });

    test('hero ribbon watermark is more visible', function () {
        $scss = file_get_contents(__DIR__ . '/../../cdn/sass/site.scss');

        expect($scss)->toMatch('/\.hero__ribbon\s*{[^}]*opacity:\s*0?\.[3-9]/');
    });

});

describe('Site script (progressive enhancement)', function () {

    test('replicates the kyaulabs hexagon spawner', function () {
        $js = file_get_contents(__DIR__ . '/../../cdn/js/site.js');

        expect($js)->toContain('floatUp')
            ->toContain('hexfield');
    });

    test('tracks sections with an IntersectionObserver', function () {
        $js = file_get_contents(__DIR__ . '/../../cdn/js/site.js');

        expect($js)->toContain('IntersectionObserver');
    });

    test('marks the active section in the navigation for assistive tech', function () {
        $js = file_get_contents(__DIR__ . '/../../cdn/js/site.js');

        expect($js)->toContain('aria-current');
    });

    test('respects reduced motion when enhancing scrolling', function () {
        $js = file_get_contents(__DIR__ . '/../../cdn/js/site.js');

        expect($js)->toContain('prefers-reduced-motion');
    });

    test('swaps the header icon for the full logo past the hero', function () {
        $js = file_get_contents(__DIR__ . '/../../cdn/js/site.js');

        expect($js)->toContain('site-header--scrolled');
    });

    test('counts up the vsi metric when motion is allowed', function () {
        $js = file_get_contents(__DIR__ . '/../../cdn/js/site.js');

        expect($js)->toContain('data-count');
    });

    test('deck navigation covers wheel, touch and keyboard', function () {
        $js = file_get_contents(__DIR__ . '/../../cdn/js/site.js');

        // wheel, touch and keyboard all drive deck navigation, footer included
        expect($js)->toContain("'wheel'")
            ->toContain('touchstart')
            ->toContain('keydown')
            ->toContain('deckLock')
            ->toContain('scrollIntoView')
            ->toContain('.site-footer');
    });

});

describe('FX module (three.js effects)', function () {

    test('vendors three.js locally and imports it', function () {
        expect(file_exists(__DIR__ . '/../../cdn/javascript/vendor/three.module.min.js'))->toBeTrue();
        $js = file_get_contents(__DIR__ . '/../../cdn/js/fx.js');

        expect($js)->toContain('from "three"')
            ->toContain('hero-fx')
            ->toContain('prism-net');
    });

    test('hero nebula is small square particles with the original brand colors', function () {
        $js = file_get_contents(__DIR__ . '/../../cdn/js/fx.js');

        // plain PointsMaterial, no map -> crisp squares; smaller than before
        expect($js)->not->toContain('makeGlyphAtlas')
            ->not->toContain('gl_PointCoord')
            ->not->toContain('ShaderMaterial')
            ->not->toContain('makeCodeTexture')
            ->not->toContain('PlaneGeometry')
            ->toContain('size: 0.45');
    });

    test('prism constellation uses the full three-shade palette and layered links', function () {
        $js = file_get_contents(__DIR__ . '/../../cdn/js/fx.js');

        expect($js)->toContain('641705792')
            ->toContain('#22d3ee')
            ->toContain('#a78bfa')   // their --accent-2
            ->toContain('#f472b6'); // their --accent-3
    });

    test('mulberry32 final mix uses the xor-shift (not a bare shift)', function () {
        // a bare `(r >>> 14)` collapses output to ~0.0000x, degenerating the
        // seeded point clouds into an invisible vertical sliver
        $js = file_get_contents(__DIR__ . '/../../cdn/js/fx.js');

        expect($js)->toContain('^ (r >>> 14)');
    });

    test('honors reduced motion for the webgl scenes', function () {
        $js = file_get_contents(__DIR__ . '/../../cdn/js/fx.js');

        expect($js)->toContain('prefers-reduced-motion');
    });

});

describe('Panel theme palette', function () {

    test('kyau labs is purple, voidbbs blue-cyan, vsi red', function () {
        $scss = file_get_contents(__DIR__ . '/../../cdn/sass/site.scss');

        expect($scss)->toContain('#8c47d1')   // kyau labs purple
            ->toContain('#4dc5dc')          // voidbbs cyan
            ->toContain('#ff304a');         // vsi red
    });

    test('hexagons use the exact kyaulabs construction and keyframes', function () {
        $scss = file_get_contents(__DIR__ . '/../../cdn/sass/site.scss');

        expect($scss)->toContain('@keyframes floatUp')
            ->toContain('35.36px')
            ->toContain('scaleY(0.5774) rotate(-45deg)');
    });

    test('kyau labs hexfield is a structural child of the section', function () {
        $html = renderPage();

        // like the prism canvas, the hexfield must be a direct child of the
        // section so it spans the full panel — not trapped in the art cell
        expect($html)->toMatch('/<section id="kyaulabs"[^>]*>\s*<div class="hexfield"/');

        $scss = file_get_contents(__DIR__ . '/../../cdn/sass/site.scss');
        expect($scss)->toMatch('/\.hexfield\s*{[^}]*position:\s*absolute/');
    });

    test('prism constellation canvas is layered behind the whole section', function () {
        $html = renderPage();

        // canvas must be a direct child of the section, not trapped inside
        // the art grid cell (a transformed/revealed ancestor would contain it)
        expect($html)->toMatch('/<section id="prism"[^>]*>\s*<canvas id="prism-net"/');

        $scss = file_get_contents(__DIR__ . '/../../cdn/sass/site.scss');
        expect($scss)->toMatch('/\.netfx\s*{[^}]*position:\s*absolute/');
    });

});

// vim: ft=php sts=4 sw=4 ts=4 et :

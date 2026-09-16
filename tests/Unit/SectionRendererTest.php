<?php

# $KYAULabs: SectionRendererTest.php kyau@aura.kyaulabs 2026/09/14 -0700 Exp $

declare(strict_types=1);

use KYAU\Net\Project;
use KYAU\Net\SectionRenderer;

function sampleProject(string $id = 'voidbbs'): Project
{
    return new Project(
        id: $id,
        name: 'voidBBS',
        url: 'https://voidbbs.com',
        tagline: 'Now accepting 300/1200/2400 baud !!',
        description: 'A modern BBS and the home of AfterMUD.',
        tags: ['BBS', 'AfterMUD', 'PHP'],
        accent: '#ffaa3e',
    );
}

describe('SectionRenderer', function () {

    test('renders a full-viewport hero panel anchored by the project id', function () {
        $html = (new SectionRenderer())->render(sampleProject(), 0);

        expect($html)->toContain('<section')
            ->toContain('id="voidbbs"')
            ->toContain('panel--voidbbs')
            ->toContain('aria-labelledby="voidbbs-title"');
    });

    test('themes every section with its own project modifier', function () {
        $renderer = new SectionRenderer();

        foreach (['voidbbs', 'kyaulabs', 'prism', 'vsi'] as $id) {
            expect($renderer->render(sampleProject($id), 0))->toContain("panel--{$id}");
        }
    });

    test('renders a heading whose id labels the section', function () {
        $html = (new SectionRenderer())->render(sampleProject(), 0);

        expect($html)->toContain('id="voidbbs-title"')
            ->toContain('<h2')
            ->toContain('voidBBS');
    });

    test('exposes the accent color as a CSS custom property', function () {
        $html = (new SectionRenderer())->render(sampleProject(), 0);

        expect($html)->toContain('--accent:#ffaa3e');
    });

    test('renders a descriptive external link with safe rel attributes', function () {
        $html = (new SectionRenderer())->render(sampleProject(), 0);

        expect($html)->toContain('href="https://voidbbs.com"')
            ->toContain('target="_blank"')
            ->toContain('rel="noopener noreferrer"')
            ->toContain('voidbbs.com');
    });

    test('renders every tag as a list item', function () {
        $html = (new SectionRenderer())->render(sampleProject(), 0);

        expect($html)->toContain('<li>BBS</li>')
            ->toContain('<li>AfterMUD</li>')
            ->toContain('<li>PHP</li>');
    });

    test('renders the project index as a two-digit number for the kicker', function () {
        $renderer = new SectionRenderer();

        expect($renderer->render(sampleProject(), 0))->toContain('01')
            ->and($renderer->render(sampleProject(), 3))->toContain('04');
    });

    test('renders bespoke art markup per project theme', function () {
        $renderer = new SectionRenderer();

        expect($renderer->render(sampleProject('kyaulabs'), 0))->toContain('hexfield')
            ->and($renderer->render(sampleProject('prism'), 1))->toContain('id="prism-net"')
            ->and($renderer->render(sampleProject('voidbbs'), 2))->toContain('term__ansi')
            ->and($renderer->render(sampleProject('vsi'), 3))->toContain('metric__value');
    });

    test('kyau labs omits the empty art cell entirely', function () {
        $html = (new SectionRenderer())->render(sampleProject('kyaulabs'), 0);

        expect($html)->not->toContain('panel__art');
    });

    test('voidbbs terminal shows a random real ansi graphic on each load', function () {
        $renderer = new SectionRenderer();

        $seen = [];
        for ($i = 0; $i < 40; $i++) {
            $html = $renderer->render(sampleProject('voidbbs'), 2);
            expect($html)->toMatch('/\/cdn\/img\/void-header-alt[12]\.png/');
            preg_match('/void-(header-alt[12])\.png/', $html, $m);
            $seen[$m[1]] = true;
        }
        // 40 rolls should surface both graphics
        expect(count($seen))->toBe(2);
    });

    test('the failed telnet login capture is retired', function () {
        // void-login.png was rendered from a telnet capture with broken
        // colors; only the converted ANSI site headers may be used
        expect(file_exists(__DIR__ . '/../../cdn/img/void-login.png'))->toBeFalse();

        $html = (new SectionRenderer())->render(sampleProject('voidbbs'), 2);
        expect($html)->not->toContain('void-login');
    });

    test('void terminal shows users online and a tabbed login prompt', function () {
        $html = (new SectionRenderer())->render(sampleProject('voidbbs'), 2);

        expect($html)->toContain('users online')
            ->toMatch('/login:\s*<span class="term__cursor"/');
    });

    test('projects with a logo render it inside the heading', function () {
        $project = new Project(
            id: 'kyaulabs',
            name: 'KYAU Labs',
            url: 'https://kyaulabs.com',
            tagline: 'tag',
            description: 'desc',
            tags: [],
            accent: '#8c47d1',
            logo: '/cdn/img/kyaulabs-logo.svg',
        );
        $html = (new SectionRenderer())->render($project, 0);

        expect($html)->toContain('<h2')
            ->toContain('class="panel__logo"')
            ->toContain('src="/cdn/img/kyaulabs-logo.svg"')
            ->toContain('alt="KYAU Labs"');
    });

    test('projects without a logo keep a text heading', function () {
        $html = (new SectionRenderer())->render(sampleProject('voidbbs'), 2);

        expect($html)->not->toContain('panel__logo')
            ->toContain('voidBBS');
    });

    test('escapes hostile markup in all user-controlled fields', function () {
        $evil = new Project(
            id: 'voidbbs', // ids are slug-validated, hostile input impossible
            name: '<script>alert(1)</script>',
            url: 'https://example.com',
            tagline: '<img src=x onerror=alert(1)>',
            description: '"><script>alert(2)</script>',
            tags: ['<b>bold</b>'],
            accent: '#41d6c3',
        );

        $html = (new SectionRenderer())->render($evil, 0);

        expect($html)->not->toContain('<script>')
            ->not->toContain('<img src=x')
            ->not->toContain('<b>bold</b>')
            ->toContain('&lt;script&gt;');
    });

    test('marks the decorative art as hidden from assistive technology', function () {
        $html = (new SectionRenderer())->render(sampleProject(), 0);

        expect($html)->toContain('aria-hidden="true"');
    });

    test('vsi art is the verified-specs metric from the live site, not a gauge replica', function () {
        $html = (new SectionRenderer())->render(sampleProject('vsi'), 3);

        expect($html)->toContain('class="metric"')
            ->toContain('data-count="403"')
            ->toContain('>403<')
            ->toContain('Verified Specs')
            ->not->toContain('diag');
    });

    test('rejects a project id with no bespoke art', function () {
        $renderer = new SectionRenderer();

        $renderer->render(sampleProject('unknown'), 0);
    })->throws(RuntimeException::class, "No art defined for project 'unknown'.");

});

// vim: ft=php sts=4 sw=4 ts=4 et :

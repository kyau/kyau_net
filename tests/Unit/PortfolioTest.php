<?php

# $KYAULabs: PortfolioTest.php kyau@aura.kyaulabs 2026/09/14 -0700 Exp $

declare(strict_types=1);

use KYAU\Net\Portfolio;
use KYAU\Net\Project;

describe('Portfolio collection', function () {

    test('contains exactly four projects', function () {
        expect(Portfolio::projects())->toHaveCount(4);
    });

    test('contains only Project instances', function () {
        expect(Portfolio::projects())->each->toBeInstanceOf(Project::class);
    });

    test('orders the projects kyaulabs, prism, voidbbs, vsi', function () {
        $ids = array_map(fn (Project $p) => $p->id, Portfolio::projects());

        expect($ids)->toBe(['kyaulabs', 'prism', 'voidbbs', 'vsi']);
    });

    test('display names are KYAU Labs, Prism, VOID, VSI', function () {
        $names = array_map(fn (Project $p) => $p->name, Portfolio::projects());

        expect($names)->toBe(['KYAU Labs', 'Prism', 'VOID', 'VSI']);
    });

    test('links to the live sites', function () {
        $urls = array_map(fn (Project $p) => $p->url, Portfolio::projects());

        expect($urls)->toBe([
            'https://kyaulabs.com',
            'https://prism.kyaulabs.com',
            'https://voidbbs.com',
            'https://vsi.kyaulabs.com',
        ]);
    });

    test('every project has a unique id and accent color', function () {
        $projects = Portfolio::projects();

        expect(array_unique(array_map(fn (Project $p) => $p->id, $projects)))->toHaveCount(4)
            ->and(array_unique(array_map(fn (Project $p) => $p->accent, $projects)))->toHaveCount(4);
    });

    test('every project carries at least two tags', function () {
        foreach (Portfolio::projects() as $project) {
            expect(count($project->tags))->toBeGreaterThanOrEqual(2);
        }
    });
    test('kyau labs is purple, prism cyan, voidbbs blue-cyan, vsi red', function () {
        $accents = array_map(fn (Project $p) => $p->accent, Portfolio::projects());

        expect($accents)->toBe(['#8c47d1', '#22d3ee', '#4dc5dc', '#ff304a']);
    });

    test('kyau labs, prism and vsi carry logo images; voidbbs keeps its text title', function () {
        $logos = array_map(fn (Project $p) => $p->logo, Portfolio::projects());

        expect($logos)->toBe([
            '/cdn/img/kyaulabs-logo.svg',
            '/cdn/img/prism-logo.png',
            null,
            '/cdn/img/vsi-logo.svg',
        ]);
    });

    test('vsi copy does not spoil the 3d surprise', function () {
        $vsi = Portfolio::projects()[3];

        expect(strtolower($vsi->description))->not->toContain('fl5');
    });

});

// vim: ft=php sts=4 sw=4 ts=4 et :

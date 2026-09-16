<?php

# $KYAULabs: Portfolio.php kyau@aura.kyaulabs 2026/09/14 -0700 Exp $

declare(strict_types=1);

namespace KYAU\Net;

/**
 * Class Portfolio
 *
 * Canonical, ordered list of projects showcased on kyau.net.
 * Copy, accent colors and logos mirror the identity of each live site.
 */
final class Portfolio
{
    /**
     * The showcased projects, in display order.
     *
     * @return list<Project>
     */
    public static function projects(): array
    {
        return [
            new Project(
                id: 'kyaulabs',
                name: 'KYAU Labs',
                url: 'https://kyaulabs.com',
                tagline: 'The lab behind it all',
                description: 'The umbrella for everything I build and run: a self-hosted homelab, custom drone and RC car builds, media, and every major open-source release — from BBS software to developer tooling.',
                tags: ['Homelab', 'Open Source', 'Drones & RC', 'Self-Hosted'],
                accent: '#8c47d1',
                logo: '/cdn/img/kyaulabs-logo.svg',
            ),
            new Project(
                id: 'prism',
                name: 'Prism',
                url: 'https://prism.kyaulabs.com',
                tagline: 'AI development, observed live',
                description: 'A coding harness for the Pi agent: TDD enforcement, conventional commits, an SCSS/JS build pipeline and Semgrep + Gitleaks security scanning — its own site renders the repository as live telemetry.',
                tags: ['AI Tooling', 'Pi SDK', 'TDD', 'Security', 'Telemetry'],
                accent: '#22d3ee',
                logo: '/cdn/img/prism-logo.png',
            ),
            new Project(
                id: 'voidbbs',
                name: 'VOID',
                url: 'https://voidbbs.com',
                tagline: 'Now accepting 300/1200/2400 baud !!',
                description: 'VOID is a bulletin board system running Worldgroup v3.30-NT — online off and on since 2004. Telnet in for message networks, door games and ANSI art, or dive into AfterMUD, a persistent multiplayer text world.',
                tags: ['BBS', 'Worldgroup', 'AfterMUD', 'ANSI Art', 'Telnet'],
                accent: '#4dc5dc',
            ),
            new Project(
                id: 'vsi',
                name: 'VSI',
                url: 'https://vsi.kyaulabs.com',
                tagline: "A technician\u{2019}s co-pilot",
                description: 'A Honda/Acura vehicle service reference built for the shop floor: fast lookups, spec sheets and workflow tools in a dark, glanceable interface designed for speed.',
                tags: ['Automotive', 'Honda/Acura', 'Tooling', 'PWA'],
                accent: '#ff304a',
                logo: '/cdn/img/vsi-logo.svg',
            ),
        ];
    }
}

// vim: ft=php sts=4 sw=4 ts=4 et :

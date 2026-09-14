<?php

# $KYAULabs: SectionRenderer.php kyau@aura.kyaulabs 2026/09/14 -0700 Exp $

declare(strict_types=1);

namespace KYAU\Net;

use RuntimeException;

/**
 * Class SectionRenderer
 *
 * Renders a single portfolio project as its own full-viewport hero panel,
 * themed after the live site it showcases — including its real logo and
 * bespoke decorative CSS art (aria-hidden): floating hexagons for KYAU
 * Labs, a HUD + WebGL constellation canvas for Prism, a Linux terminal
 * displaying genuine voidBBS ANSI graphics, and a diagnostic gauge for VSI.
 */
final class SectionRenderer
{
    /** @var list<string> Real ANSI graphics captured from / used by voidBBS */
    private const VOID_ART = [
        '/cdn/img/void-login.png',       // the actual login screen, captured live over telnet
        '/cdn/img/void-header-alt1.png', // site header, ANSI -> PNG
        '/cdn/img/void-header-alt2.png', // site header, ANSI -> PNG
    ];

    /**
     * Render one project hero panel.
     *
     * @param Project $project The project to render.
     * @param int $index Zero-based position, drives the numeric kicker.
     * @return string The section HTML.
     */
    public function render(Project $project, int $index): string
    {
        $id = self::e($project->id);
        $number = str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT);
        $url = self::e($project->url);
        $host = self::e($project->host());
        $tagline = self::e($project->tagline);
        $description = self::e($project->description);
        $accent = self::e($project->accent);
        $title = $project->logo !== null
            ? sprintf('<img class="panel__logo" src="%s" alt="%s" />', self::e($project->logo), self::e($project->name))
            : self::e($project->name);
        $tags = implode('', array_map(
            static fn (string $tag): string => "\n\t\t\t\t\t<li>" . self::e($tag) . '</li>',
            $project->tags,
        ));
        $art = $this->art($project->id);
        $backdrop = match ($project->id) {
            // full-bleed layers live as DIRECT CHILDREN of the section so no
            // grid cell or transformed ancestor can trap their containing block
            'kyaulabs' => "\n            " . $this->hexfield(),
            'prism' => "\n            <canvas id=\"prism-net\" class=\"netfx\" aria-hidden=\"true\"></canvas>",
            'vsi' => "\n            <span class=\"bolt bolt--left\" aria-hidden=\"true\"></span><span class=\"bolt bolt--right\" aria-hidden=\"true\"></span>",
            default => '',
        };
        $artBlock = $art === '' ? '' : <<<HTML

                <div class="panel__art art--{$id} reveal" style="--reveal-delay:.25s" aria-hidden="true">
{$art}
                </div>
HTML;

        return <<<HTML
        <section id="{$id}" class="panel panel--{$id}" style="--accent:{$accent}" aria-labelledby="{$id}-title">{$backdrop}
            <div class="panel__inner">
                <div class="panel__content">
                    <p class="panel__kicker reveal" aria-hidden="true">{$number} // {$host}</p>
                    <h2 class="panel__title reveal" id="{$id}-title" style="--reveal-delay:.1s">{$title}</h2>
                    <p class="panel__tagline reveal" style="--reveal-delay:.2s">{$tagline}</p>
                    <p class="panel__description reveal" style="--reveal-delay:.3s">{$description}</p>
                    <ul class="panel__tags reveal" style="--reveal-delay:.4s">{$tags}
                    </ul>
                    <p class="reveal" style="--reveal-delay:.5s"><a class="panel__link" href="{$url}" target="_blank" rel="noopener noreferrer">Visit {$host}<span class="panel__link-arrow" aria-hidden="true">&#8599;</span><span class="visually-hidden"> (opens in a new tab)</span></a></p>
                </div>{$artBlock}
            </div>
        </section>
HTML;
    }

    /**
     * Section-level hexagon field for KYAU Labs (static hexagons as a
     * no-JS / reduced-motion fallback; site.js spawns the animated ones).
     *
     * @return string The hexfield HTML.
     */
    private function hexfield(): string
    {
        return <<<'HTML'
            <div class="hexfield" aria-hidden="true">
                <span class="hexagon hexagon--static" style="left:8%;--scale:1.1;--opacity-hex:.4;--dur:34s"></span>
                <span class="hexagon hexagon--static" style="left:24%;--scale:.7;--opacity-hex:.28;top:62%;--dur:27s;--del:6s"></span>
                <span class="hexagon hexagon--static" style="left:47%;--scale:1.4;--opacity-hex:.22;top:18%;--dur:41s;--del:12s"></span>
                <span class="hexagon hexagon--static" style="left:68%;--scale:.9;--opacity-hex:.45;top:70%;--dur:30s;--del:3s"></span>
                <span class="hexagon hexagon--static" style="left:82%;--scale:1.2;--opacity-hex:.3;top:30%;--dur:36s;--del:9s"></span>
                <span class="hexagon hexagon--static" style="left:93%;--scale:.6;--opacity-hex:.4;top:55%;--dur:24s;--del:2s"></span>
            </div>
HTML;
    }

    /**
     * Bespoke decorative art markup per project theme.
     *
     * @param string $id The project id.
     * @return string The art HTML (always inside an aria-hidden container).
     */
    private function art(string $id): string
    {
        return match ($id) {
            // KYAU Labs — the hexagon field is a section-level backdrop;
            // no art cell content
            'kyaulabs' => '',
            // Prism — HUD frame (the constellation canvas is a direct child
            // of the section so it layers behind ALL panel content)
            'prism' => <<<'HTML'
                    <div class="hud">
                        <span class="hud__corner hud__corner--tl"></span>
                        <span class="hud__corner hud__corner--tr"></span>
                        <span class="hud__corner hud__corner--bl"></span>
                        <span class="hud__corner hud__corner--br"></span>
                        <div class="hud__stats">
                            <span class="hud__stat"><b>TDD</b>enforced</span>
                            <span class="hud__stat"><b>CI</b>passing</span>
                            <span class="hud__stat"><b>SEMGREP</b>clean</span>
                        </div>
                        <div class="hud__ticker"><span class="hud__ticker-track">feat: tdd enforcement &middot; test: harness specs &middot; ci: semgrep + gitleaks &middot; feat: scss pipeline &middot; chore(release): v1.4.0 &middot; fix: session resume &middot;&nbsp;feat: tdd enforcement &middot; test: harness specs &middot; ci: semgrep + gitleaks &middot; feat: scss pipeline &middot; chore(release): v1.4.0 &middot; fix: session resume &middot;&nbsp;</span></div>
                    </div>
HTML,
            // voidBBS — a Linux terminal displaying a real ANSI graphic (random on each load)
            'voidbbs' => $this->voidTerminal(),
            // VSI — diagnostic gauge over carbon fiber
            'vsi' => <<<'HTML'
                    <div class="diag">
                        <div class="diag__gauge"><span class="diag__needle"></span></div>
                        <div class="diag__chips">
                            <span class="diag__chip diag__chip--alert">VTEC</span>
                            <span class="diag__chip">OBD-II</span>
                            <span class="diag__chip diag__chip--alert">SVC DUE</span>
                            <span class="diag__chip">K20C1</span>
                        </div>
                    </div>
HTML,
            default => throw new RuntimeException("No art defined for project '{$id}'."),
        };
    }

    /**
     * Linux terminal showing a genuine voidBBS ANSI graphic, picked at random.
     *
     * @return string The terminal HTML.
     */
    private function voidTerminal(): string
    {
        $img = self::VOID_ART[random_int(0, count(self::VOID_ART) - 1)];

        return <<<HTML
                    <div class="term">
                        <div class="term__bar">
                            <span class="term__title">kyau@void: ~ &mdash; telnet voidbbs.com</span>
                            <span class="term__btns"><i class="term__btn term__btn--min"></i><i class="term__btn term__btn--max"></i><i class="term__btn term__btn--close"></i></span>
                        </div>
                        <div class="term__screen">
                            <img class="term__ansi" src="{$img}" alt="" width="640" height="400" loading="lazy" />
                            <span class="term__status"><b>AfterMUD</b>: 12 users online
        login: <span class="term__cursor"></span></span>
                        </div>
                    </div>
HTML;
    }

    /**
     * Escape a string for safe HTML output.
     *
     * @param string $value The raw value.
     * @return string The escaped value.
     */
    private static function e(string $value): string
    {
        return htmlentities($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

// vim: ft=php sts=4 sw=4 ts=4 et :

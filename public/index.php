<?php

# $KYAULabs: index.php kyau@aura.kyaulabs 2026/09/14 -0700 Exp $

declare(strict_types=1);

use KYAU\Net\Portfolio;
use KYAU\Net\SectionRenderer;

$rus = getrusage();
require_once(__DIR__ . '/../vendor/autoload.php');
require_once(__DIR__ . '/../aurora/aurora.inc.php');

$site = new KYAULabs\Aurora(template: 'index.html', cdn: '/cdn', status: false, html: true, templateDir: __DIR__ . '/../templates');
$site->title = 'kyau.net — Sean Bruen · Web Design & Development';
$site->description = 'Portfolio of Sean Bruen (kyau): voidBBS & AfterMUD, KYAU Labs, Prism, and the VSI Helper — scrolling showcase of web design and development.';
$site->css = [
    __DIR__ . '/../cdn/css/site.min.css' => '/cdn/css/site.min.css',
];
$site->mjs = [
    __DIR__ . '/../cdn/javascript/site.min.js' => '/cdn/javascript/site.min.js',
    __DIR__ . '/../cdn/javascript/fx.min.js' => '/cdn/javascript/fx.min.js',
];

$site->htmlHeader();
?>
    <a class="skip-link" href="#main">Skip to content</a>
    <div class="scroll-progress" aria-hidden="true"></div>

    <header class="site-header">
        <a class="site-header__brand" href="#top" aria-label="kyau.net — back to top"><img class="site-header__icon" src="/cdn/img/brand/kyau-icon.svg" alt="" width="24" height="24" /><img class="site-header__logo" src="/cdn/img/brand/kyau-logo.svg" alt="" height="28" /></a>
        <a class="site-header__contact" href="#contact">Contact</a>
    </header>

    <nav class="site-nav" aria-label="Portfolio sections">
        <ol>
<?php foreach (Portfolio::projects() as $i => $project): ?>
            <li><a href="#<?= $project->id ?>" style="--dot-accent:<?= $project->accent ?>"><span class="site-nav__dot" aria-hidden="true"></span><span class="site-nav__label"><?= $project->name ?></span></a></li>
<?php endforeach; ?>
        </ol>
    </nav>

    <main id="main">

        <section id="top" class="hero" aria-labelledby="hero-title">
            <canvas id="hero-fx" class="hero__fx" aria-hidden="true"></canvas>
            <img class="hero__ribbon" src="/cdn/img/brand/kyau-icon.svg" alt="" aria-hidden="true" />
            <div>
                <p class="hero__eyebrow reveal" style="--reveal-delay:.05s">Sean Bruen — kyau.net</p>
                <h1 class="hero__title reveal" id="hero-title" style="--reveal-delay:.15s">Design. Code.<br /><em>Worlds.</em></h1>
                <p class="hero__lead reveal" style="--reveal-delay:.3s">Web designer &amp; developer building everything from BBS doorways and MUDs to AI tooling and shop-floor apps. Scroll to step through the portfolio.</p>
            </div>
            <a class="hero__cue" href="#voidbbs">Scroll to explore <svg viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M8 2v10m0 0 4-4m-4 4-4-4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg></a>
        </section>

<?php
$renderer = new SectionRenderer();
foreach (Portfolio::projects() as $i => $project) {
    echo $renderer->render($project, $i), "\n\n";
}
?>
        <section id="contact" class="contact" aria-labelledby="contact-title">
            <div>
                <h2 class="contact__title reveal" id="contact-title">Let&rsquo;s build <em>something</em>.</h2>
                <p class="contact__lead reveal" style="--reveal-delay:.15s">Open source, homelab experiments, BBS culture, and the occasional tool that makes a workday faster. Find me around the web:</p>
                <ul class="contact__links reveal" style="--reveal-delay:.3s">
                    <li><a href="https://github.com/kyau" target="_blank" rel="noopener noreferrer">@kyau<span class="visually-hidden"> on GitHub (opens in a new tab)</span></a></li>
                    <li><a href="https://github.com/kyaulabs" target="_blank" rel="noopener noreferrer">@kyaulabs<span class="visually-hidden"> on GitHub (opens in a new tab)</span></a></li>
                    <li><a href="https://discord.gg/DSvUNYm" target="_blank" rel="noopener noreferrer">Discord<span class="visually-hidden"> (opens in a new tab)</span></a></li>
                    <li><a href="mailto:git@kyaulabs.com">git@kyaulabs.com</a></li>
                </ul>
                <p><img class="contact__lockup reveal" style="--reveal-delay:.45s" src="/cdn/img/brand/kyau-logo.svg" alt="KYAU — カイヨ" /></p>
            </div>
        </section>

    </main>

    <footer class="site-footer">
        <p class="site-footer__logos">
            <a href="https://kyaulabs.com" target="_blank" rel="noopener noreferrer"><img src="/cdn/img/kyaulabs-logo.svg" alt="KYAU Labs" height="26" /></a>
            <a href="https://github.com/kyaulabs/aurora" target="_blank" rel="noopener noreferrer"><img src="/cdn/img/brand/aurora-logo.svg" alt="Built with Aurora" height="26" /></a>
        </p>
        <p class="visually-hidden">&copy; <?= gmdate('Y') ?> Sean Bruen · KYAU Labs · Built with Aurora <?= $site->version() ?></p>
    </footer>
<?php
$site->htmlFooter();
echo $site->comment($rus, $_SERVER['SCRIPT_FILENAME'] ?? __FILE__, true);

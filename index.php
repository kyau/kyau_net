<?php

# $KYAULabs: index.php kyau@aura.kyaulabs 2026/09/14 -0700 Exp $

/**
 * Front controller shim — allows serving the repository root as the
 * document root while the actual page lives in /public (Aurora resolves
 * the CDN directory relative to the calling script's directory).
 */

declare(strict_types=1);

require __DIR__ . '/public/index.php';

// vim: ft=php sts=4 sw=4 ts=4 et :

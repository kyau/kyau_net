<?php

# $KYAULabs: Project.php kyau@aura.kyaulabs 2026/09/14 -0700 Exp $

declare(strict_types=1);

namespace KYAU\Net;

use InvalidArgumentException;

/**
 * Class Project
 *
 * Immutable value object describing a single portfolio project.
 */
final class Project
{
    /**
     * @var list<string> $tags
     */
    public readonly array $tags;

    /**
     * Project constructor.
     *
     * @param string $id URL-safe slug, also used as the section anchor.
     * @param string $name Display name of the project.
     * @param string $url Canonical HTTPS URL of the project.
     * @param string $tagline Short one-line pitch.
     * @param string $description Longer descriptive paragraph.
     * @param list<string> $tags Technology / category tags.
     * @param string $accent Accent color as a 6-digit hex value (e.g. "#41d6c3").
     * @param string|null $logo Optional logo image, local /cdn path only.
     * @throws InvalidArgumentException When any field fails validation.
     */
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $url,
        public readonly string $tagline,
        public readonly string $description,
        array $tags,
        public readonly string $accent,
        public readonly ?string $logo = null,
    ) {
        if ($id === '' || preg_match('/\A[a-z0-9]+(?:-[a-z0-9]+)*\z/', $id) !== 1) {
            throw new InvalidArgumentException("Project id must be a non-empty slug, got '{$id}'.");
        }
        if ($name === '') {
            throw new InvalidArgumentException('Project name must not be empty.');
        }
        if (filter_var($url, FILTER_VALIDATE_URL) === false || !str_starts_with($url, 'https://')) {
            throw new InvalidArgumentException("Project url must be a valid https URL, got '{$url}'.");
        }
        if ($tagline === '') {
            throw new InvalidArgumentException('Project tagline must not be empty.');
        }
        if ($description === '') {
            throw new InvalidArgumentException('Project description must not be empty.');
        }
        if (preg_match('/\A#[0-9a-fA-F]{6}\z/', $accent) !== 1) {
            throw new InvalidArgumentException("Project accent must be a 6-digit hex color, got '{$accent}'.");
        }
        if ($logo !== null && preg_match('/\A\/cdn\/[a-z0-9\/.\-]+\z/i', $logo) !== 1) {
            throw new InvalidArgumentException("Project logo must be a local /cdn path, got '{$logo}'.");
        }
        $this->tags = array_values($tags);
    }

    /**
     * Bare host name of the project URL, without scheme or leading "www.".
     *
     * @return string The display host (e.g. "voidbbs.com").
     */
    public function host(): string
    {
        $host = (string) parse_url($this->url, PHP_URL_HOST);
        return (string) preg_replace('/\Awww\./', '', $host);
    }
}

// vim: ft=php sts=4 sw=4 ts=4 et :

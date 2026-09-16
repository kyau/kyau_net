<?php

# $KYAULabs: ProjectTest.php kyau@aura.kyaulabs 2026/09/14 -0700 Exp $

declare(strict_types=1);

use KYAU\Net\Project;

describe('Project value object', function () {

    test('exposes all of its properties', function () {
        $project = new Project(
            id: 'voidbbs',
            name: 'voidBBS',
            url: 'https://voidbbs.com',
            tagline: 'A bulletin board system reborn',
            description: 'A modern BBS and the home of AfterMUD.',
            tags: ['PHP', 'Telnet'],
            accent: '#8b5cf6',
        );

        expect($project->id)->toBe('voidbbs')
            ->and($project->name)->toBe('voidBBS')
            ->and($project->url)->toBe('https://voidbbs.com')
            ->and($project->tagline)->toBe('A bulletin board system reborn')
            ->and($project->description)->toBe('A modern BBS and the home of AfterMUD.')
            ->and($project->tags)->toBe(['PHP', 'Telnet'])
            ->and($project->accent)->toBe('#8b5cf6');
    });

    test('rejects an empty name', function () {
        new Project('voidbbs', '', 'https://voidbbs.com', 'tag', 'desc', [], '#8b5cf6');
    })->throws(InvalidArgumentException::class);

    test('rejects an empty id', function () {
        new Project('', 'voidBBS', 'https://voidbbs.com', 'tag', 'desc', [], '#8b5cf6');
    })->throws(InvalidArgumentException::class);

    test('rejects an id that is not a slug', function () {
        new Project('Void BBS!', 'voidBBS', 'https://voidbbs.com', 'tag', 'desc', [], '#8b5cf6');
    })->throws(InvalidArgumentException::class);

    test('rejects a non-https url', function () {
        new Project('voidbbs', 'voidBBS', 'http://voidbbs.com', 'tag', 'desc', [], '#8b5cf6');
    })->throws(InvalidArgumentException::class);

    test('rejects a malformed accent color', function () {
        new Project('voidbbs', 'voidBBS', 'https://voidbbs.com', 'tag', 'desc', [], 'purple');
    })->throws(InvalidArgumentException::class);

    test('rejects an empty tagline', function () {
        new Project('voidbbs', 'voidBBS', 'https://voidbbs.com', '', 'desc', [], '#8b5cf6');
    })->throws(InvalidArgumentException::class);

    test('rejects an empty description', function () {
        new Project('voidbbs', 'voidBBS', 'https://voidbbs.com', 'tag', '', [], '#8b5cf6');
    })->throws(InvalidArgumentException::class);

    test('exposes the bare host for display purposes', function () {
        $project = new Project('voidbbs', 'voidBBS', 'https://voidbbs.com', 'tag', 'desc', [], '#4dc5dc');

        expect($project->host())->toBe('voidbbs.com');
    });

    test('host strips the www prefix', function () {
        $project = new Project('example', 'Example', 'https://www.example.com', 'tag', 'desc', [], '#41d6c3');

        expect($project->host())->toBe('example.com');
    });
    test('logo defaults to null and accepts a path', function () {
        $bare = new Project('voidbbs', 'voidBBS', 'https://voidbbs.com', 'tag', 'desc', [], '#4dc5dc');
        $logo = new Project('vsi', 'VSI', 'https://vsi.kyaulabs.com', 'tag', 'desc', [], '#ff304a', '/cdn/img/vsi-logo.svg');

        expect($bare->logo)->toBeNull()
            ->and($logo->logo)->toBe('/cdn/img/vsi-logo.svg');
    });

    test('rejects a logo that is not a local /cdn path', function () {
        new Project('vsi', 'VSI', 'https://vsi.kyaulabs.com', 'tag', 'desc', [], '#ff304a', 'https://evil.com/x.svg');
    })->throws(InvalidArgumentException::class);

});

// vim: ft=php sts=4 sw=4 ts=4 et :

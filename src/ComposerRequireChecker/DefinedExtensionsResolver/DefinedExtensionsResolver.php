<?php

declare(strict_types=1);

namespace ComposerRequireChecker\DefinedExtensionsResolver;

use function array_keys;
use function array_merge;
use function assert;
use function file_get_contents;
use function is_string;
use function json_decode;
use function str_starts_with;
use function substr;

class DefinedExtensionsResolver
{
    /**
     * @param array<string> $phpCoreExtensions
     *
     * @return array<string>
     */
    public function __invoke(string $composerJson, array $phpCoreExtensions = []): array
    {
        $composerJsonContents = file_get_contents($composerJson);
        assert(is_string($composerJsonContents));

        /** @var array<string, string> $requires */
        $requires = json_decode($composerJsonContents, true)['require'] ?? [];

        $extensions           = [];
        $addPhpCoreExtensions = false;

        foreach (array_keys($requires) as $require) {
            if ($require === 'php' || $require === 'php-64bit') {
                $addPhpCoreExtensions = true;
                continue;
            }

            if (! str_starts_with($require, 'ext-')) {
                continue;
            }

            $extensions[] = substr($require, 4);
        }

        if ($addPhpCoreExtensions) {
            $extensions = array_merge($extensions, $phpCoreExtensions);
        }

        return $extensions;
    }
}

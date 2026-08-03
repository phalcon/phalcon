<?php

/**
 * Quill configuration - https://github.com/phalcon/quill
 *
 * `source`, `output` and `assets` are relative to this file; `repository`,
 * `branch` and `prefix` build the "Source on GitHub" link on every class.
 *
 * `output` and `assets` mirror the documentation site's own layout, so
 * `cp -r nikos/docs/* <documentation>/docs/` lands the pages and the
 * stylesheet where each belongs.
 */

declare(strict_types=1);

return [
    'language'   => 'php',
    'source'     => 'src',
    'output'     => 'nikos/docs/api',
    'assets'     => 'nikos/docs/assets/css',
    'repository' => 'phalcon/phalcon',
    'branch'     => 'v6.0.x',
    'prefix'     => 'src',
    'extension'  => 'php',
    'namespace'  => 'Phalcon',
];

<?php

/**
 * Scribe configuration - https://github.com/phalcon/scribe
 *
 * `source` and `output` are relative to this file; `repository`, `branch` and
 * `prefix` build the "Source on GitHub" link on every class.
 */

declare(strict_types=1);

return [
    'language'   => 'php',
    'source'     => 'src',
    'output'     => 'nikos/api',
    'repository' => 'phalcon/phalcon',
    'branch'     => 'v6.0.x',
    'prefix'     => 'src',
    'extension'  => 'php',
];

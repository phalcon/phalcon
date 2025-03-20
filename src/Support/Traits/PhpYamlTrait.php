<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Support\Traits;

use function yaml_parse_file;

trait PhpYamlTrait
{
    /**
     * Parse a YAML stream from a file
     *
     * @param string                  $filename
     * @param int                     $pos
     * @param int|null                $ndocs
     * @param array<string, callable> $callbacks
     *
     * @return mixed
     *
     * @link https://php.net/manual/en/function.yaml-parse-file.php
     */
    protected function phpYamlParseFile(
        string $filename,
        int $pos = 0,
        int | null &$ndocs = null,
        array $callbacks = []
    ) {
        return yaml_parse_file($filename, $pos, $ndocs, $callbacks);
    }
}

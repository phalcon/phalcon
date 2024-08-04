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

namespace Phalcon\Tests\Fixtures\Config\Adapter;

use Phalcon\Config\Adapter\Yaml;

class YamlParseFileFixture extends Yaml
{
    /**
     * Parse a YAML stream from a file
     *
     * @param string $filename
     * @param int    $pos
     * @param int    $ndocs
     * @param array  $callbacks
     *
     * @return mixed
     *
     * @link https://php.net/manual/en/function.yaml-parse-file.php
     */
    protected function phpYamlParseFile(
        $filename,
        $pos = 0,
        &$ndocs = null,
        $callbacks = []
    ) {
        return false;
    }
}

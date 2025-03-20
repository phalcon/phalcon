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

namespace Phalcon\Assets\Asset;

use Phalcon\Assets\Asset as AssetBase;

/**
 * Represents CSS assets
 *
 * Class Css
 *
 * @package Phalcon\Assets\Asset
 */
class Css extends AssetBase
{
    /**
     * Css constructor.
     *
     * @param string                $path
     * @param bool                  $local
     * @param bool                  $filter
     * @param array<string, string> $attributes
     * @param string|null           $version
     * @param bool                  $autoVersion
     */
    public function __construct(
        string $path,
        bool $local = true,
        bool $filter = true,
        array $attributes = [],
        string | null $version = null,
        bool $autoVersion = false
    ) {
        parent::__construct('css', $path, $local, $filter, $attributes, $version, $autoVersion);
    }
}

<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Html\Helper;

use Phalcon\Html\Exception;

use function array_merge;

/**
 * Class Img
 */
class Img extends AbstractHelper
{
    /**
     * Produce a <img> tag.
     *
     * @param string $src
     * @param array  $attributes
     *
     * @return string
     * @throws Exception
     */
    public function __invoke(string $src, array $attributes = [])
    {
        $overrides = ["src" => $src];

        /**
         * Avoid duplicate "src" and ignore it if it is passed in the attributes
         */
        unset($attributes["src"]);

        $overrides = array_merge($overrides, $attributes);

        return $this->selfClose("img", $overrides);
    }
}

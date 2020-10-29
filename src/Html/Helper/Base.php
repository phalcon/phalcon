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
 * Class Base
 */
class Base extends AbstractHelper
{
    /**
     * Produce a `<base/>` tag.
     *
     * @param string $href
     * @param array  $attributes
     *
     * @return string
     * @throws Exception
     */
    public function __invoke(string $href, array $attributes = [])
    {
        if (!empty($href)) {
            $overrides = ["href" => $href];
        } else {
            $overrides = [];
        }

        /**
         * Avoid duplicate "href" and ignore it if it is passed in the attributes
         */
        unset($attributes["href"]);

        $overrides = array_merge($overrides, $attributes);

        return $this->renderElement("base", $overrides);
    }
}

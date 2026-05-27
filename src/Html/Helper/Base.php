<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Html\Helper;

/**
 * Class Base
 */
class Base extends AbstractHelper
{
    /**
     * Produce a `<base/>` tag.
     *
     * @param string|null $href
     * @param array       $attributes
     *
     * @return string
     */
    public function __invoke(?string $href = null, array $attributes = []): string
    {
        if (!empty($href)) {
            $attributes = $this->injectAttribute('href', $href, $attributes);
        } else {
            unset($attributes['href']);
        }

        return $this->renderElement('base', $attributes);
    }
}

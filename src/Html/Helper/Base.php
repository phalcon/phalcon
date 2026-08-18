<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Html\Helper;

use Phalcon\Contracts\Html\HtmlTypes;

/**
 * Class Base
 *
 * @phpstan-import-type html_attributes from HtmlTypes
 */
class Base extends AbstractHelper
{
    /**
     * Produce a `<base/>` tag.
     *
     * @param string|null $href
     * @phpstan-param html_attributes $attributes
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

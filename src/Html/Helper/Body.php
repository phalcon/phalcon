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
 * Class Body
 *
 * @package Phalcon\Html\Helper
 */
class Body extends AbstractHelper
{
    /**
     * Produce a `<body>` tag.
     *
     * @param array $attributes
     *
     * @return string
     */
    public function __invoke(array $attributes = []): string
    {
        return $this->renderElement('body', $attributes);
    }
}

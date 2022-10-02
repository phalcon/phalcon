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

namespace Phalcon\Html\Attributes;

/**
 * Rendering interface for HTML attributes
 */
interface RenderInterface
{
    /**
     * Generate a string representation
     */
    public function render(): string;
}

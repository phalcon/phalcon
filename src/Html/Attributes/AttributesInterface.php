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

use Phalcon\Html\Attributes;

/**
 * Html Attributes Interface
 */
interface AttributesInterface
{
    /**
     * Get Attributes
     *
     * @return Attributes
     */
    public function getAttributes(): Attributes;

    /**
     * Set Attributes
     *
     * @param Attributes $attributes
     *
     * @return AttributesInterface
     */
    public function setAttributes(Attributes $attributes): AttributesInterface;
}

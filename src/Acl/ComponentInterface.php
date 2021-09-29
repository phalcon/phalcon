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

namespace Phalcon\Acl;

/**
 * Interface for Phalcon\Acl\Component
 */
interface ComponentInterface
{
    /**
     * Returns the component name
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Returns component description
     *
     * @return string|null
     */
    public function getDescription(): ?string;

    /**
     * Magic method __toString
     *
     * @return string
     */
    public function __toString(): string;
}

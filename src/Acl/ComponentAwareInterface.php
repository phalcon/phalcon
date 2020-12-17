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
 * Interface for classes which could be used in allow method as RESOURCE
 *
 * @package Phalcon\Acl
 */
interface ComponentAwareInterface
{
    /**
     * Returns component name
     */
    public function getComponentName(): string;
}

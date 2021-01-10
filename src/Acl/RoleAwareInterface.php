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

namespace Phiz\Acl;

/**
 * Interface for classes which could be used in allow method as ROLE
 *
 * @package Phiz\Acl
 */
interface RoleAwareInterface
{
    /**
     * Returns role name
     */
    public function getRoleName(): string;
}

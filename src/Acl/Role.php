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

use Phiz\Acl\Traits\ItemTrait;

/**
 * This class defines role entity and its description
 */
class Role implements RoleInterface
{
    use ItemTrait;

    /**
     * Role constructor.
     *
     * @param string      $name
     * @param string|null $description
     *
     * @throws Exception
     */
    public function __construct(string $name, string $description = null)
    {
        if ('*' === $name) {
            throw new Exception("Role name cannot be '*'");
        }

        $this->name        = $name;
        $this->description = $description;
    }
}

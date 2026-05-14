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

use Phalcon\Acl\Exceptions\ForbiddenWildcard;
use Phalcon\Acl\Traits\ItemTrait;

/**
 * Components of the ACL. Also known as "Resources"
 */
class Component implements ComponentInterface
{
    use ItemTrait;

    /**
     * Component constructor.
     *
     * @param string $name
     * @param string $description
     *
     * @throws ForbiddenWildcard
     */
    public function __construct(string $name, string $description = '')
    {
        if ('*' === $name) {
            throw new ForbiddenWildcard('component');
        }

        $this->name        = $name;
        $this->description = $description;
    }
}

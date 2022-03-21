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

namespace Phalcon\Html\Link;

/**
 * A link provider object.
 */
interface LinkProviderInterface
{
    /**
     * Returns an array of LinkInterface objects.
     *
     * @return LinkInterface[]
     */
    public function getLinks(): array;

    /**
     * Returns an array of LinkInterface objects that have a specific relationship.
     *
     * @return LinkInterface[]
     */
    public function getLinksByRel(string $rel): array;
}

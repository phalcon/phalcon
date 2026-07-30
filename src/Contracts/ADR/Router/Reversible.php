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

namespace Phalcon\Contracts\ADR\Router;

/**
 * Optional capability contract for a router that can name the HTTP method an
 * Action class answers. Callers detect support with `instanceof`.
 *
 * Separate from Router rather than a member of it: adding a member to a
 * published interface breaks every implementor.
 *
 * Completes the inverse that pathFor() begins. Without it a caller holding an
 * Action class can recover the path but not the method, and the only way to
 * reach the method is to re-derive the naming rule - which means a second copy
 * of the convention outside this component.
 */
interface Reversible
{
    /**
     * The HTTP method the given Action class answers, uppercased, or null when
     * the class is not one this convention would have produced.
     *
     * Mirrors pathFor(): same argument, same null semantics, so a caller that
     * accepts one answer accepts the other.
     */
    public function methodFor(string $className): ?string;
}

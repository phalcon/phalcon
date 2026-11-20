<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Unit\Container;

use Phalcon\Container\Resolver\Lazy\Env;
use Phalcon\Talon\PHPUnit\AbstractUnitTestCase;
use stdClass;

/**
 * cphalcon uses the PHP weak type rules for an int value that goes to a string
 * target. cphalcon returns the int with no change, phalcon returns it as a
 * string, thus the test compares the values with assertEquals().
 *
 * @issue https://github.com/phalcon/phalcon/issues/887
 */
final class WeakModeIntValuesTest extends AbstractUnitTestCase
{
    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-20
     */
    public function testContainerResolverLazyEnvValueInt(): void
    {
        $_ENV['PH_WEAK_887'] = 123;

        try {
            $this->assertEquals(
                123,
                (new Env('PH_WEAK_887'))->resolve(new stdClass())
            );
        } finally {
            unset($_ENV['PH_WEAK_887']);
        }
    }
}

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

namespace Phalcon\Tests\Unit\Paginator;

use Phalcon\Paginator\Exception;
use Phalcon\Paginator\PaginatorFactory;
use Phalcon\Talon\PHPUnit\AbstractUnitTestCase;

/**
 * cphalcon uses the PHP weak type rules for an int value that goes to a string
 * target, thus the int becomes a string.
 *
 * @issue https://github.com/phalcon/phalcon/issues/887
 */
final class WeakModeIntValuesTest extends AbstractUnitTestCase
{
    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-20
     */
    public function testPaginatorPaginatorFactoryLoadAdapterInt(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Service 1 is not registered');

        (new PaginatorFactory())->load(['adapter' => 1]);
    }
}

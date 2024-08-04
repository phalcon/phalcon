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

namespace Phalcon\Tests\Unit\Support\Collection\ReadOnlyCollection;

use Phalcon\Support\Collection\ReadOnlyCollection;
use Phalcon\Tests\UnitTestCase;

final class ConstructTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Support\Collection\ReadOnlyCollection :: __construct()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testSupportCollectionConstruct(): void
    {
        $collection = new ReadOnlyCollection();

        $class = ReadOnlyCollection::class;
        $this->assertInstanceOf($class, $collection);
    }
}

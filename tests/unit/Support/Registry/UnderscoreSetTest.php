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

namespace Phalcon\Tests\Unit\Support\Registry;

use Phalcon\Support\Registry;
use Phalcon\Tests\AbstractUnitTestCase;

final class UnderscoreSetTest extends AbstractUnitTestCase
{
    /**
     * Unit Tests Phalcon\Support\Registry :: __set()
     *
     * @author Sid Roberts <https://github.com/SidRoberts>
     * @since  2019-05-25
     */
    public function testSupportRegistryUnderscoreSet(): void
    {
        $registry = new Registry();


        $registry->three = 'Phalcon';

        $this->assertSame(
            'Phalcon',
            $registry->get('three')
        );
    }
}

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
use PHPUnit\Framework\Attributes\Test;

final class GetIteratorTest extends AbstractRegistryTestCase
{
    /**
     * Tests Phalcon\Support\Registry :: getIterator()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    #[Test]
    public function testSupportRegistryGetIterator(): void
    {
        $data = $this->getData();
        $registry = new Registry($data);

        foreach ($registry as $key => $value) {
            $this->assertSame($data[$key], $registry[$key]);
        }
    }
}

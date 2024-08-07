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

namespace Phalcon\Tests\Database\Db\RawValue;

use Phalcon\Db\RawValue;
use Phalcon\Tests\DatabaseTestCase;

final class GetValueTest extends DatabaseTestCase
{
    /**
     * Tests Phalcon\Db\RawValue :: getValue()
     *
     * @author Sid Roberts <https://github.com/SidRoberts>
     * @since  2019-04-17
     *
     * @group  common
     */
    public function testDbRawvalueGetValue(): void
    {
        $rawValue = new RawValue('example string');

        $this->assertEquals(
            'example string',
            $rawValue->getValue()
        );
    }
}
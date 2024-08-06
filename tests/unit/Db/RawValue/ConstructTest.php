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

namespace Phalcon\Tests\Unit\Db\RawValue;

use Codeception\Example;
use Phalcon\Tests\DatabaseTestCase;
use Phalcon\Db\RawValue;

final class ConstructTest extends DatabaseTestCase
{
    /**
     * Tests Phalcon\Db\RawValue :: __construct()
     *
     * @author       Sid Roberts <https://github.com/SidRoberts>
     * @since        2019-04-17
     *
     * @dataProvider valueProvider
     *
     * @group        common
     */
    public function dbRawvalueConstruct(
        mixed $value,
        string $expected
    ): void {
        $rawValue = new RawValue($value);

        $this->assertEquals($expected, $rawValue->getValue());
    }

    public static function valueProvider(): array
    {
        return [
            [
                'hello',
                'hello',
            ],

            [
                null,
                'NULL',
            ],

            [
                123,
                '123',
            ],

            [
                '',
                "''",
            ],
        ];
    }
}

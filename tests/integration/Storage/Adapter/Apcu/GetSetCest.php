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

namespace Phalcon\Tests\Integration\Storage\Adapter\Apcu;

use Codeception\Example;
use Phalcon\Support\Exception;
use Phalcon\Storage\Adapter\Apcu;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Support\HelperFactory;
use Phalcon\Tests\Fixtures\Traits\ApcuTrait;
use stdClass;
use UnitTester;

class GetSetCest
{
    use ApcuTrait;

    /**
     * Tests Phalcon\Storage\Adapter\Apcu :: get()
     *
     * @dataProvider getExamples
     *
     * @param UnitTester $I
     * @param Example    $example
     *
     * @throws Exception
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function storageAdapterApcuGetSet(UnitTester $I, Example $example)
    {
        $I->wantToTest('Storage\Adapter\Apcu - get()/set() - ' . $example[0]);

        $helper     = new HelperFactory();
        $serializer = new SerializerFactory();
        $adapter    = new Apcu($helper, $serializer);

        $key = uniqid();

        $result = $adapter->set($key, $example[1]);
        $I->assertTrue($result);

        $expected = $example[1];
        $actual   = $adapter->get($key);
        $I->assertEquals($expected, $actual);
    }

    private function getExamples(): array
    {
        return [
            [
                'string',
                'random string',
            ],
            [
                'integer',
                123456,
            ],
            [
                'float',
                123.456,
            ],
            [
                'boolean',
                true,
            ],
            [
                'object',
                new stdClass(),
            ],
        ];
    }
}

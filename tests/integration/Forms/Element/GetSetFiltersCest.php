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

namespace Phalcon\Tests\Integration\Forms\Element;

use Codeception\Example;
use IntegrationTester;
use Phalcon\Tests\Fixtures\Traits\FormsTrait;

use function array_flip;
use function uniqid;

class GetSetFiltersCest
{
    use FormsTrait;

    /**
     * Tests Phalcon\Forms\Element\* :: getFilters()/setFilters()/addFilter()
     *
     * @dataProvider getExamples
     *
     * @param IntegrationTester $I
     * @param Example           $example
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2021-12-05
     */
    public function formsElementGetSetAddFilters(IntegrationTester $I, Example $example)
    {
        $I->wantToTest(
            'Forms\Element\* - getFilters()/setFilters()/addFilter() - '
            . $example[0]
        );

        $name    = uniqid();
        $data    = [
            'trim',
            'striptags',
        ];
        $flipped = array_flip($data);

        $class  = $example[1];
        $object = new $class($name);

        $expected = [];
        $actual   = $object->getFilters();
        $I->assertSame($expected, $actual);

        $object->setFilters($flipped);

        $expected = $flipped;
        $actual   = $object->getFilters();
        $I->assertSame($expected, $actual);

        $object->setFilters('lower');

        $expected = ['lower'];
        $actual   = $object->getFilters();
        $I->assertSame($expected, $actual);

        $object->addFilter('trim');

        $expected = ['lower', 'trim'];
        $actual   = $object->getFilters();
        $I->assertSame($expected, $actual);
    }
}

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

use function uniqid;

class GetSetNameClearCest
{
    use FormsTrait;

    /**
     * Tests Phalcon\Forms\Element\* :: getName()/setName()/clear()
     *
     * @dataProvider getExamples
     *
     * @param IntegrationTester $I
     * @param Example           $example
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2021-12-05
     */
    public function formsElementGetSetNameClear(IntegrationTester $I, Example $example)
    {
        $I->wantToTest('Forms\Element\* - getName()/setName()/clear() - ' . $example[0]);

        $name   = uniqid();
        $class  = $example[1];
        $object = new $class($name);

        $expected = $name;
        $actual   = $object->getName();
        $I->assertSame($expected, $actual);

        $different = uniqid();
        $object->setName($different);

        $expected = $different;
        $actual   = $object->getName();
        $I->assertSame($expected, $actual);

        $object->clear();

        $actual = $object->getForm();
        $I->assertNull($actual);
    }
}

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

class GetSetDefaultCest
{
    use FormsTrait;

    /**
     * Tests Phalcon\Forms\Element\* :: getDefault()/setDefault()
     *
     * @dataProvider getExamples
     *
     * @param IntegrationTester $I
     * @param Example           $example
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2021-12-05
     */
    public function formsElementGetSetDefault(IntegrationTester $I, Example $example)
    {
        $I->wantToTest('Forms\Element\* - getDefault()/setDefault() - ' . $example[0]);

        $name   = uniqid();
        $class  = $example[1];
        $object = new $class($name);

        $actual = $object->getDefault();
        $I->assertNull($actual);

        $object->setDefault($name);

        $expected = $name;
        $actual   = $object->getDefault();
        $I->assertSame($expected, $actual);
    }
}

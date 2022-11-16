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

class GetSetAttributeCest
{
    use FormsTrait;

    /**
     * Tests Phalcon\Forms\Element\* :: getAttribute()/setAttribute()
     *
     * @dataProvider getExamples
     *
     * @param IntegrationTester $I
     * @param Example           $example
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2021-12-05
     */
    public function formsElementGetSetAttribute(IntegrationTester $I, Example $example)
    {
        $I->wantToTest('Forms\Element\* - getAttribute()/setAttribute() - ' . $example[0]);

        $name = uniqid();
        $data = [
            'one'   => 'two',
            'three' => 'four',
        ];

        $class  = $example[1];
        $object = new $class($name);

        $expected = 'fallback';
        $actual   = $object->getAttribute('one', 'fallback');
        $I->assertSame($expected, $actual);

        $object = new $class($name, $data);

        $expected = 'two';
        $actual   = $object->getAttribute('one', 'fallback');
        $I->assertSame($expected, $actual);

        $object->setAttribute('one', 'four');

        $expected = 'four';
        $actual   = $object->getAttribute('one', 'fallback');
        $I->assertSame($expected, $actual);
    }
}

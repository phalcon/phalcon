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

class GetSetUserOptionsCest
{
    use FormsTrait;

    /**
     * Tests Phalcon\Forms\Element\* :: getUserOptions()/setUserOptions()
     *
     * @dataProvider getExamples
     *
     * @param IntegrationTester $I
     * @param Example           $example
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2021-12-05
     */
    public function formsElementGetSetUserOptions(IntegrationTester $I, Example $example)
    {
        $I->wantToTest('Forms\Element\* - getUserOptions()/setUserOptions() - ' . $example[0]);

        $name    = uniqid();
        $data    = [
            'one'   => 'two',
            'three' => 'four',
        ];
        $flipped = array_flip($data);

        $class  = $example[1];
        $object = new $class($name);

        $expected = [];
        $actual   = $object->getUserOptions();
        $I->assertSame($expected, $actual);

        $object->setUserOptions($flipped);

        $expected = $flipped;
        $actual   = $object->getUserOptions();
        $I->assertSame($expected, $actual);
    }
}

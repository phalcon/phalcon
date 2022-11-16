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

class GetSetUserOptionCest
{
    use FormsTrait;

    /**
     * Tests Phalcon\Forms\Element\* :: getUserOption()/setUserOption()
     *
     * @dataProvider getExamples
     *
     * @param IntegrationTester $I
     * @param Example           $example
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2021-12-05
     */
    public function formsElementGetSetUserOption(IntegrationTester $I, Example $example)
    {
        $I->wantToTest('Forms\Element\* - getUserOption()/setUserOption() - ' . $example[0]);

        $name   = uniqid();
        $class  = $example[1];
        $object = new $class($name);

        $expected = 'fallback';
        $actual   = $object->getUserOption('one', 'fallback');
        $I->assertSame($expected, $actual);

        $object->setUserOption('one', 'four');

        $expected = 'four';
        $actual   = $object->getUserOption('one', 'fallback');
        $I->assertSame($expected, $actual);
    }

}

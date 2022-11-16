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
use Phalcon\Forms\Form;
use Phalcon\Tests\Fixtures\Traits\FormsTrait;

use function uniqid;

class GetSetFormCest
{
    use FormsTrait;

    /**
     * Tests Phalcon\Forms\Element\* :: getForm()/setForm()
     *
     * @dataProvider getExamples
     *
     * @param IntegrationTester $I
     * @param Example           $example
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2021-12-05
     */
    public function formsElementGetSetForm(IntegrationTester $I, Example $example)
    {
        $I->wantToTest('Forms\Element\* - getForm()/setForm() - ' . $example[0]);

        $name   = uniqid();
        $class  = $example[1];
        $object = new $class($name);
        $form   = new Form();

        $object->setForm($form);

        $expected = $form;
        $actual   = $object->getForm();
        $I->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Forms\Element\* :: getForm()/add()
     *
     * @dataProvider getExamples
     *
     * @param IntegrationTester $I
     * @param Example           $example
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2021-12-05
     */
    public function formsElementGetFormAdd(IntegrationTester $I, Example $example)
    {
        $I->wantToTest('Forms\Element\* - getForm()/add() - ' . $example[0]);

        $name   = uniqid();
        $class  = $example[1];
        $object = new $class($name);
        $form   = new Form();

        $object->setForm($form);

        $expected = $form;
        $actual   = $object->getForm();
        $I->assertSame($expected, $actual);
    }
}

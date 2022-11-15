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

namespace Phalcon\Tests\Integration\Forms\Form;

use IntegrationTester;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Form;
use stdClass;

/**
 * Class GetFilteredValueCest
 */
class GetFilteredValueCest
{
    /**
     * Tests Phalcon\Forms\Form :: getFilteredValue()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2021-11-03
     */
    public function testGetFilteredValueWithoutEntity(IntegrationTester $I)
    {
        $I->wantToTest('Forms\Form - getFilteredValue()');

        $data = ['firstName' => ' test '];
        $form = $this->getForm($data);

        $expected = $data['firstName'];
        $actual   = $form->getValue("firstName");
        $I->assertSame($expected, $actual);

        $expected = "TEST";
        $actual   = $form->getFilteredValue("firstName");
        $I->assertSame($expected, $actual);
    }

    public function testGetFilteredValueWithEntity(IntegrationTester $I)
    {
        $I->wantToTest('Forms\Form - getFilteredValue()');

        $entity = new stdClass();
        $data = ['firstName' => ' test '];

        $form = $this->getForm($data, $entity);

        $expected = $data['firstName'];
        $actual   = $form->getValue("firstName");
        $I->assertSame($expected, $actual);

        $expected = "TEST";
        $actual   = $form->getFilteredValue("firstName");
        $I->assertSame($expected, $actual);
    }

    private function getForm(array $data, $entity = null): Form
    {
        $form = new Form();
        $firstNameTag = new Text('firstName');
        $form->add(
            $firstNameTag->setFilters([
                'upper',
                'regex' => ["/^\s+|\s+$|\s+(?=\s)/", ''],
            ])->setLabel('Firstname')
        );

        $form->isValid($data, $entity);

        return $form;
    }
}

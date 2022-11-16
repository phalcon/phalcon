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
use Phalcon\Html\Escaper;
use Phalcon\Html\TagFactory;
use Phalcon\Tests\Fixtures\Traits\FormsTrait;

use function uniqid;

class GetSetLabelCest
{
    use FormsTrait;

    /**
     * Tests Phalcon\Forms\Element\* :: getLabel()/setLabel()/label()
     *
     * @dataProvider getExamples
     *
     * @param IntegrationTester $I
     * @param Example           $example
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2021-12-05
     */
    public function formsElementGetSetLabel(IntegrationTester $I, Example $example)
    {
        $I->wantToTest('Forms\Element\* - getLabel()/setLabel()/label() - ' . $example[0]);

        $name    = uniqid();
        $class   = $example[1];
        $factory = new TagFactory(new Escaper());
        $object  = new $class($name);
        $object->setTagFactory($factory);

        $actual = $object->getLabel();
        $I->assertNull($actual);

        $object->setLabel($name);

        $expected = $name;
        $actual   = $object->getLabel();
        $I->assertSame($expected, $actual);

        $attributes = ['class' => 'label label-warning'];
        $expected   = '<label for="' . $name
            . '" class="label label-warning">' . $name . '</label>';
        $actual     = $object->label($attributes);
        $I->assertSame($expected, $actual);
    }
}

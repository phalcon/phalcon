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

namespace Phalcon\Tests\Unit\Forms\Element;

use Phalcon\Forms\Form;
use Phalcon\Tests\Fixtures\Traits\FormsTrait;
use Phalcon\Tests\UnitTestCase;

use function uniqid;

final class GetSetFormTest extends UnitTestCase
{
    use FormsTrait;

    /**
     * Tests Phalcon\Forms\Element\* :: getForm()/add()
     *
     * @dataProvider getExamples
     *
     *
     * @return void
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2021-12-05
     */
    public function testFormsElementGetFormAdd(
        string $class
    ): void {
        $name   = uniqid();
        $object = new $class($name);
        $form   = new Form();

        $object->setForm($form);

        $expected = $form;
        $actual   = $object->getForm();
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Forms\Element\* :: getForm()/setForm()
     *
     * @dataProvider getExamples
     *
     * @return void
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2021-12-05
     */
    public function testFormsElementGetSetForm(
        string $class
    ): void {
        $name   = uniqid();
        $object = new $class($name);
        $form   = new Form();

        $object->setForm($form);

        $expected = $form;
        $actual   = $object->getForm();
        $this->assertSame($expected, $actual);
    }
}

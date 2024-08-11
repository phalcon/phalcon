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

use Phalcon\Tests\Fixtures\Traits\FormsTrait;
use Phalcon\Tests\AbstractUnitTestCase;

use function uniqid;

final class GetSetNameClearTest extends AbstractUnitTestCase
{
    use FormsTrait;

    /**
     * Tests Phalcon\Forms\Element\* :: getName()/setName()/clear()
     *
     * @dataProvider getExamples
     *
     * @return void
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2021-12-05
     */
    public function testFormsElementGetSetNameClear(
        string $class
    ): void {
        $name   = uniqid();
        $object = new $class($name);

        $expected = $name;
        $actual   = $object->getName();
        $this->assertSame($expected, $actual);

        $different = uniqid();
        $object->setName($different);

        $expected = $different;
        $actual   = $object->getName();
        $this->assertSame($expected, $actual);

        $object->clear();

        $actual = $object->getForm();
        $this->assertNull($actual);
    }
}

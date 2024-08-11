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

final class GetSetDefaultTest extends AbstractUnitTestCase
{
    use FormsTrait;

    /**
     * Tests Phalcon\Forms\Element\* :: getDefault()/setDefault()
     *
     * @dataProvider getExamples
     *
     * @return void
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2021-12-05
     */
    public function testFormsElementGetSetDefault(
        string $class
    ): void {
        $name   = uniqid();
        $object = new $class($name);

        $actual = $object->getDefault();
        $this->assertNull($actual);

        $object->setDefault($name);

        $expected = $name;
        $actual   = $object->getDefault();
        $this->assertSame($expected, $actual);
    }
}

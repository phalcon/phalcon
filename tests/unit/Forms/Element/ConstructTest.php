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

final class ConstructTest extends AbstractUnitTestCase
{
    use FormsTrait;

    /**
     * Tests Phalcon\Forms\Element\* :: __construct()
     *
     * @dataProvider getExamplesWithoutSelect
     *
     * @return void
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2021-12-05
     */
    public function testFormsElementTextareaConstruct(
        string $class
    ): void {
        $name   = uniqid();
        $object = new $class($name);

        $expected = $name;
        $actual   = $object->getName();
        $this->assertSame($expected, $actual);

        $name       = uniqid();
        $attributes = ["one" => "two"];
        $object     = new $class($name, $attributes);

        $expected = $name;
        $actual   = $object->getName();
        $this->assertSame($expected, $actual);

        $expected = $attributes;
        $actual   = $object->getAttributes();
        $this->assertSame($expected, $actual);
    }
}

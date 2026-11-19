<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Unit\Forms;

use Phalcon\Html\Attributes;
use Phalcon\Talon\PHPUnit\AbstractUnitTestCase;
use Phalcon\Tests\Unit\Forms\Fake\FakeElementRedeclared;
use Phalcon\Tests\Unit\Forms\Fake\FakeFormRedeclared;

final class RedeclaredPropertiesTest extends AbstractUnitTestCase
{
    /**
     * An element can declare its value with no type, as in cphalcon.
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     *
     * @issue  https://github.com/phalcon/phalcon/issues/840
     */
    public function testFormsElementRedeclaredValue(): void
    {
        $element = new FakeElementRedeclared('name');

        $this->assertSame('name', $element->getName());
    }

    /**
     * A form can declare its entity and attributes with no type, as in
     * cphalcon.
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     *
     * @issue  https://github.com/phalcon/phalcon/issues/840
     */
    public function testFormsFormRedeclaredEntityAndAttributes(): void
    {
        $form = new FakeFormRedeclared();

        $this->assertInstanceOf(Attributes::class, $form->getAttributes());
        $this->assertNull($form->getEntity());
    }
}

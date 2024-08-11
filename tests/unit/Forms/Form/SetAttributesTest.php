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

namespace Phalcon\Tests\Unit\Forms\Form;

use Phalcon\Forms\Form;
use Phalcon\Tests\AbstractUnitTestCase;

final class SetAttributesTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Forms\Form :: setAttributes()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-05-11
     */
    public function testFormsFormSetAttributes(): void
    {
        $form = new Form();

        $actual = method_exists($form, 'setAttributes');

        $this->assertTrue($actual);
    }
}

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

use Phalcon\Tests\UnitTestCase;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Form;

/**
 * Class CountTest extends UnitTestCase
 */
final class CountTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Forms\Form :: count()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-04-18
     */
    public function testFormsFormCount(): void
    {
        $form = new Form();

        $this->assertCount(0, $form);

        $form->add(
            new Text('name')
        );

        $form->add(
            new Text('telephone')
        );

        $this->assertCount(2, $form);
    }
}

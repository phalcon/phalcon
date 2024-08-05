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
use Phalcon\Tests\UnitTestCase;

final class GetUserOptionTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Forms\Form :: getUserOption()
     *
     * @author Sid Roberts <https://github.com/SidRoberts>
     * @since  2019-05-23
     */
    public function testFormsFormGetUserOption(): void
    {
        $userOptions = [
            'some' => 'value',
        ];

        $form = new Form();

        $form->setUserOptions($userOptions);

        $this->assertEquals(
            $userOptions,
            $form->getUserOptions()
        );

        $this->assertEquals(
            'value',
            $form->getUserOption('some')
        );

        $this->assertNull(
            $form->getUserOption('some-non')
        );
    }

    /**
     * Tests Phalcon\Forms\Form :: getUserOption() with default value
     *
     * @author Sid Roberts <https://github.com/SidRoberts>
     * @since  2019-05-23
     */
    public function testFormsFormGetUserOptionDefaultValue(): void
    {
        $form = new Form();

        $form->setUserOption('some', 'value');

        $this->assertEquals(
            'value',
            $form->getUserOption('some', 'default')
        );

        $this->assertEquals(
            'default',
            $form->getUserOption('some-non', 'default')
        );
    }
}

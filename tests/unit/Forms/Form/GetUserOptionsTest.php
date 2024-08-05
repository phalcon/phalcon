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
use Phalcon\Forms\Form;

final class GetUserOptionsTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Forms\Form :: getUserOptions()
     *
     * @author Sid Roberts <https://github.com/SidRoberts>
     * @since  2019-05-23
     */
    public function testFormsFormGetUserOptions(): void
    {
        $userOptions = [
            'some' => 'value',
        ];

        $form = new Form(null, $userOptions);

        $this->assertEquals(
            $userOptions,
            $form->getUserOptions()
        );
    }
}

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

namespace Phalcon\Tests\Unit\Forms\Manager;

use Phalcon\Tests\UnitTestCase;
use Phalcon\Forms\Exception;
use Phalcon\Forms\Form;
use Phalcon\Forms\Manager;

final class GetSetTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Forms\Manager :: get() / set()
     *
     * @author Sid Roberts <https://github.com/SidRoberts>
     * @since  2019-05-23
     */
    public function testFormsManagerGetSet(): void
    {
        $manager = new Manager();

        $loginForm = new Form();

        $manager->set('login', $loginForm);

        $expected = $loginForm;
        $actual   = $manager->get('login');
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Forms\Manager :: get() non-existent form
     *
     * @author Sid Roberts <https://github.com/SidRoberts>
     * @since  2019-05-23
     */
    public function testFormsManagerGetNonexistentForm(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("There is no form with name='login'");

        $manager = new Manager();
        $loginForm = $manager->get('login');
    }
}

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

namespace Phalcon\Tests\Unit\Filter\Validation;

use Phalcon\Tests\UnitTestCase;
use Phalcon\Filter\Validation;
use stdClass;

final class SetEntityTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Filter\Validation :: setEntity()
     *
     * @author Sid Roberts <https://github.com/SidRoberts>
     * @since  2019-04-17
     */
    public function testFilterValidationSetEntity(): void
    {
        $user = new stdClass();
        $validation = new Validation();

        $validation->setEntity($user);

        $this->assertSame(
            $user,
            $validation->getEntity()
        );
    }
}

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

use Phalcon\Filter\Validation;
use Phalcon\Tests\UnitTestCase;
use stdClass;

final class GetEntityTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Filter\Validation :: getEntity()
     *
     * @author Sid Roberts <https://github.com/SidRoberts>
     * @since  2019-04-17
     */
    public function testFilterValidationGetEntity(): void
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

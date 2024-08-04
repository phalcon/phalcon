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

final class BindTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Filter\Validation :: bind()
     *
     * @author Sid Roberts <https://github.com/SidRoberts>
     * @since  2019-04-17
     */
    public function testFilterValidationBind(): void
    {
        $user = new stdClass();

        $data = [
            'name' => 'Sid',
            'city' => 'Busan',
        ];

        $validation = new Validation();

        $validation->bind($user, $data);

        $this->assertSame(
            $user,
            $validation->getEntity()
        );

        $this->assertSame(
            $data,
            $validation->getData()
        );
    }
}

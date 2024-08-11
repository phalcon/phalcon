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
use Phalcon\Tests\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\Test;
use stdClass;

final class BindTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Filter\Validation :: bind()
     *
     * @author Sid Roberts <https://github.com/SidRoberts>
     * @since  2019-04-17
     */
    #[Test]
    public function testFilterValidationBind(): void
    {
        $user = new stdClass();

        $data = [
            'name' => 'Leonidas',
            'city' => 'Sparta',
        ];

        $validation = new Validation();

        $validation->bind($user, $data);

        $expected = $user;
        $actual   = $validation->getEntity();
        $this->assertSame($expected, $actual);

        $expected = $data;
        $actual   = $validation->getData();
        $this->assertSame($expected, $actual);
    }
}

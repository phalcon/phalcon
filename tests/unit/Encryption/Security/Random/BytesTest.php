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

namespace Phalcon\Tests\Unit\Encryption\Security\Random;

use Phalcon\Encryption\Security\Random;
use Phalcon\Tests\UnitTestCase;

use function strlen;

final class BytesTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Encryption\Security\Random :: bytes()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testEncryptionSecurityRandomBytes(): void
    {
        $random = new Random();

        $bytes = $random->bytes();
        $this->assertSame(16, strlen($bytes));

        $bytes = $random->bytes(32);
        $this->assertSame(32, strlen($bytes));
    }
}

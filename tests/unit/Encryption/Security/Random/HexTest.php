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
use Phalcon\Tests\AbstractUnitTestCase;

final class HexTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Encryption\Security\Random :: hex()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testEncryptionSecurityRandomHex(): void
    {
        $random = new Random();
        $hex    = $random->hex();

        // Test forbidden characters
        $this->assertMatchesRegularExpression('/^[0-9a-f]+$/', $hex);

        // Default length is 16 bytes
        $this->assertSame(16, strlen($hex) / 2); // Hex is 2 characters


        $differentString = $random->hex();

        // Buy lottery ticket if this fails (or fix the bug)
        $this->assertNotEquals($hex, $differentString);


        $expectedLength = 30;
        $hex            = $random->hex($expectedLength);

        $this->assertSame($expectedLength, strlen($hex) / 2); // Hex is 2 characters
    }
}

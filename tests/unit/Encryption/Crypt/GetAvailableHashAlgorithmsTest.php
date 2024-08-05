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

namespace Phalcon\Tests\Unit\Encryption\Crypt;

use Phalcon\Encryption\Crypt;
use Phalcon\Tests\Fixtures\Encryption\Crypt\CryptFunctionExistsTwiceFixture;
use Phalcon\Tests\UnitTestCase;

use function function_exists;
use function hash_hmac_algos;

final class GetAvailableHashAlgorithmsTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Encryption\Crypt :: getAvailableHashAlgorithms()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2021-10-18
     */
    public function testEncryptionCryptGetAvailableHashAlgorithms(): void
    {
        if (true === function_exists("hash_hmac_algos")) {
            $crypt = new Crypt();

            $expected = hash_hmac_algos();
            $actual   = $crypt->getAvailableHashAlgorithms();
            $this->assertSame($expected, $actual);
        }

        $crypt    = new CryptFunctionExistsTwiceFixture();
        $expected = hash_algos();
        $actual   = $crypt->getAvailableHashAlgorithms();
        $this->assertSame($expected, $actual);
    }
}

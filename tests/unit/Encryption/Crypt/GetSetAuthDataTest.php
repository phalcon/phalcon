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
use Phalcon\Tests\UnitTestCase;

use function uniqid;

final class GetSetAuthDataTest extends UnitTestCase
{
    /**
     * Unit Tests Phalcon\Encryption\Crypt :: getAuthData()/setAuthData()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2021-10-18
     */
    public function testEncryptionCryptGetSetAuthData(): void
    {
        $crypt = new Crypt();

        $data = uniqid('d-');
        $crypt->setAuthData($data);

        $expected = $data;
        $actual   = $crypt->getAuthData();
        $this->assertSame($expected, $actual);
    }
}
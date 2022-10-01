<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Unit\Encryption\Crypt;

use Phalcon\Encryption\Crypt;
use UnitTester;

class GetSetKeyCest
{
    /**
     * Tests Phalcon\Encryption\Crypt :: getKey()/setKey()
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2021-10-18
     */
    public function encryptionCryptGetSetKey(UnitTester $I)
    {
        $I->wantToTest('Encryption\Crypt - getKey()/setKey()');

        $crypt = new Crypt();

        $I->assertSame('', $crypt->getKey());

        $crypt->setKey('123456');

        $I->assertSame(
            '123456',
            $crypt->getKey()
        );
    }
}

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

namespace Phalcon\Tests\Unit\Encryption\Security;

use Codeception\Example;
use Phalcon\Encryption\Security;
use Phalcon\Tests\UnitTestCase;

final class CheckHashTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Security :: checkHash()
     *
     * @dataProvider getExamples
     *
     * @return void
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2020-09-09
     */
    public function testEncryptionSecurityCheckHash(int $hash): void
    {
        $security = new Security();
        $password = 'PhalconROCKS!';

        $security->setDefaultHash($hash);

        $actual = $security->checkHash($password, $security->hash($password));
        $this->assertTrue($actual);
    }

    /**
     * Tests Phalcon\Security :: checkHash() - false
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testEncryptionSecurityCheckHashFalse(): void
    {
        $security = new Security();
        $password = 'PhalconROCKS!';

        $actual = $security->checkHash($password, $password, 2);
        $this->assertFalse($actual);
    }

    /**
     * @return array[]
     */
    public static function getExamples(): array
    {
        return [
            [Security::CRYPT_ARGON2I],
            [Security::CRYPT_ARGON2ID],
            [Security::CRYPT_BCRYPT],
            [Security::CRYPT_DEFAULT],
            [Security::CRYPT_BLOWFISH],
            [Security::CRYPT_BLOWFISH_A],
            [Security::CRYPT_BLOWFISH_X],
            [Security::CRYPT_BLOWFISH_Y],
            [Security::CRYPT_MD5],
            [Security::CRYPT_SHA256],
            [Security::CRYPT_SHA512],
        ];
    }
}

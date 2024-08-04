<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Unit\Encryption\Security\JWT\Builder;

use Phalcon\Encryption\Security\JWT\Builder;
use Phalcon\Encryption\Security\JWT\Exceptions\ValidatorException;
use Phalcon\Encryption\Security\JWT\Signer\Hmac;
use Phalcon\Tests\UnitTestCase;

final class GetSetExpirationTimeTest extends UnitTestCase
{
    /**
     * Unit Tests Phalcon\Encryption\Security\JWT\Builder ::
     * getExpirationTime()/setExpirationTime()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testEncryptionSecurityJWTBuilderGetSetExpirationTime(): void
    {
        $signer  = new Hmac();
        $builder = new Builder($signer);

        $this->assertNull($builder->getExpirationTime());

        $future = strtotime("now") + 1000;
        $return = $builder->setExpirationTime($future);
        $this->assertInstanceOf(Builder::class, $return);

        $this->assertSame($future, $builder->getExpirationTime());
    }

    /**
     * Unit Tests Phalcon\Encryption\Security\JWT\Builder ::
     * getExpirationTime()/setExpirationTime() - exception
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testEncryptionSecurityJWTBuilderGetSetExpirationTimeException(): void
    {
        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage("Invalid Expiration Time");

        $signer  = new Hmac();
        $builder = new Builder($signer);
        $builder->setExpirationTime(4);
    }
}

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

final class GetSetAudienceTest extends UnitTestCase
{
    /**
     * Unit Tests Phalcon\Encryption\Security\JWT\Builder ::
     * getAudience()/setAudience()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testEncryptionSecurityJWTBuilderGetSetAudience(): void
    {
        $signer  = new Hmac();
        $builder = new Builder($signer);

        $this->assertNull($builder->getAudience());

        $return = $builder->setAudience('audience');
        $this->assertInstanceOf(Builder::class, $return);

        $this->assertSame(['audience'], $builder->getAudience());

        $return = $builder->setAudience(['audience']);
        $this->assertInstanceOf(Builder::class, $return);

        $this->assertSame(['audience'], $builder->getAudience());
    }

    /**
     * Unit Tests Phalcon\Encryption\Security\JWT\Builder :: setAudience() -
     * exception
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testEncryptionSecurityJWTBuilderSetAudienceException(): void
    {
        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage("Invalid Audience");

        $signer  = new Hmac();
        $builder = new Builder($signer);
        $builder->setAudience(1234);
    }
}

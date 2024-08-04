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
use Phalcon\Encryption\Security\JWT\Signer\Hmac;
use Phalcon\Tests\UnitTestCase;

final class GetSetIdTest extends UnitTestCase
{
    /**
     * Unit Tests Phalcon\Encryption\Security\JWT\Builder :: getId()/setId()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testEncryptionSecurityJWTBuilderGetSetId(): void
    {
        $signer  = new Hmac();
        $builder = new Builder($signer);

        $this->assertNull($builder->getId());

        $return = $builder->setId('id');
        $this->assertInstanceOf(Builder::class, $return);

        $this->assertSame('id', $builder->getId());
    }
}

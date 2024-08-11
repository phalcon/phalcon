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
use Phalcon\Tests\AbstractUnitTestCase;

final class GetClaimsTest extends AbstractUnitTestCase
{
    /**
     * Unit Tests Phalcon\Encryption\Security\JWT\Builder :: getClaims()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testEncryptionSecurityJWTBuilderGetClaims(): void
    {
        $signer  = new Hmac();
        $builder = new Builder($signer);

        $this->assertEmpty($builder->getClaims());

        $builder = new Builder($signer);
        $builder
            ->addClaim('aud', 'Phalcon')
            ->addClaim('xyz', 'Other')
        ;

        $expected = [
            'aud' => 'Phalcon',
            'xyz' => 'Other',
        ];
        $actual   = $builder->getClaims();
        $this->assertSame($expected, $actual);
    }
}

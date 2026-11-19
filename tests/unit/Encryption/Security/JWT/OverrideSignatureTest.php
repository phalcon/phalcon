<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Unit\Encryption\Security\JWT;

use Phalcon\Encryption\Security\JWT\Signer\Hmac;
use Phalcon\Talon\PHPUnit\AbstractUnitTestCase;
use Phalcon\Tests\Unit\Encryption\Fake\FakeBuilderOverride;

final class OverrideSignatureTest extends AbstractUnitTestCase
{
    /**
     * A builder can override setClaim() with the cphalcon signature.
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     *
     * @issue  https://github.com/phalcon/phalcon/issues/841
     */
    public function testEncryptionSecurityJWTBuilderSetClaimOverride(): void
    {
        $builder = new FakeBuilderOverride(new Hmac());

        $builder->setSubject('phalcon');

        $this->assertSame('phalcon', $builder->getSubject());
    }
}

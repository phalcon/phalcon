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

namespace Phalcon\Tests\Unit\Encryption;

use Phalcon\Encryption\Security;
use Phalcon\Encryption\Security\JWT\Builder;
use Phalcon\Encryption\Security\JWT\Signer\Hmac;
use Phalcon\Encryption\Security\JWT\Token\Enum;
use Phalcon\Encryption\Security\JWT\Validator;
use Phalcon\Encryption\Security\Uuid;
use Phalcon\Talon\PHPUnit\AbstractUnitTestCase;

use function restore_error_handler;
use function set_error_handler;

/**
 * cphalcon uses the PHP weak type rules for an int value that goes to a string
 * target, thus the int becomes a string.
 *
 * @issue https://github.com/phalcon/phalcon/issues/886
 */
final class WeakModeIntValuesTest extends AbstractUnitTestCase
{
    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     */
    public function testEncryptionSecurityCheckTokenIntValue(): void
    {
        $this->assertFalse((new Security())->checkToken('key', 123));
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     */
    public function testEncryptionSecurityJwtValidatorIntClaim(): void
    {
        $token = (new Builder(new Hmac()))
            ->setId('123')
            ->setIssuer('456')
            ->setSubject('789')
            ->setPassphrase('&vsJBETaizP3A3VX&TPMJUqi48fJEgN7')
            ->getToken()
        ;

        $validator = new Validator($token);
        $validator->set(Enum::ID, 123);
        $validator->set(Enum::ISSUER, 456);
        $validator->set(Enum::SUBJECT, 789);

        $this->assertSame([], $token->validate($validator));
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     */
    public function testEncryptionSecurityUuidInvalidNamespace(): void
    {
        set_error_handler(
            function (): bool {
                return true;
            }
        );

        try {
            $uuid = (string) (new Uuid())->v3('not-a-uuid', 'name');
        } finally {
            restore_error_handler();
        }

        $this->assertSame('b068931c-c450-342b-a3f5-b3d276ea4297', $uuid);
    }
}

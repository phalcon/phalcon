<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Unit\Encryption\Security\JWT\Token\Item;

use Phalcon\Encryption\Security\JWT\Token\Item;
use Phalcon\Tests\UnitTestCase;

final class ConstructTest extends UnitTestCase
{
    /**
     * Unit Tests Phalcon\Encryption\Security\JWT\Token\Item :: __construct()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testEncryptionSecurityJWTTokenItemConstruct(): void
    {
        $item = new Item(['sub' => 'subject'], 'encoded-string-here');

        $this->assertInstanceOf(Item::class, $item);
    }
}

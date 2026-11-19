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

namespace Phalcon\Tests\Unit\Encryption\Security;

use Phalcon\Encryption\Security;
use Phalcon\Talon\PHPUnit\AbstractUnitTestCase;

final class HashTest extends AbstractUnitTestCase
{
    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     *
     * @issue  https://github.com/phalcon/phalcon/issues/871
     */
    public function testEncryptionSecurityHashCostNumericString(): void
    {
        $security = new Security();

        $hash = $security->hash('secret', ['cost' => '5']);

        $this->assertSame(5, $security->getHashInformation($hash)['options']['cost']);
        $this->assertTrue($security->checkHash('secret', $hash));
    }
}

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

namespace Phalcon\Tests\Unit\Storage;

use Phalcon\Storage\Adapter\Weak;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Talon\PHPUnit\AbstractUnitTestCase;
use stdClass;

/**
 * cphalcon uses the PHP weak type rules for an int value that goes to a string
 * target, thus the int becomes a string.
 *
 * @issue https://github.com/phalcon/phalcon/issues/900
 */
final class WeakModeIntValuesTest extends AbstractUnitTestCase
{
    /**
     * @issue  https://github.com/phalcon/phalcon/issues/900
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-20
     */
    public function testStorageAdapterWeakGetKeysIntKey(): void
    {
        $adapter = new Weak(new SerializerFactory());
        $object  = new stdClass();
        $adapter->set('2024', $object);
        $adapter->set('2025x', $object);

        /**
         * cphalcon skips an int key
         */
        $this->assertSame(['2025x'], $adapter->getKeys('20'));
    }
}

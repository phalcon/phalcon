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

namespace Phalcon\Tests\Unit\Config;

use Phalcon\Config\ConfigFactory;
use Phalcon\Config\Exception;
use Phalcon\Config\Exceptions\CannotLoadConfigFile;
use Phalcon\Talon\PHPUnit\AbstractUnitTestCase;

use function restore_error_handler;
use function set_error_handler;

/**
 * cphalcon uses the PHP weak type rules for an int value that goes to a string
 * target, thus the int becomes a string.
 *
 * @issue https://github.com/phalcon/phalcon/issues/887
 */
final class WeakModeIntValuesTest extends AbstractUnitTestCase
{
    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-20
     */
    public function testConfigConfigFactoryLoadAdapterInt(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Service 1 is not registered');

        (new ConfigFactory())->load(['adapter' => 1, 'filePath' => 'x.ini']);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-20
     */
    public function testConfigConfigFactoryLoadFilePathInt(): void
    {
        $this->expectException(CannotLoadConfigFile::class);
        $this->expectExceptionMessage('Configuration file 123.ini cannot be loaded');

        /**
         * parse_ini_file() gives a warning for the missing file
         */
        set_error_handler(static fn (): bool => true);

        try {
            (new ConfigFactory())->load(['adapter' => 'ini', 'filePath' => 123]);
        } finally {
            restore_error_handler();
        }
    }
}

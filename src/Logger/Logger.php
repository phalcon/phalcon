<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Logger;

use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;
use Stringable;

/**
 * A logger, with various adapters and formatters. A formatter
 * interface is available as well as an adapter one. Adapters can be created
 * easily using the built-in AdapterFactory. A LoggerFactory is also available
 * that allows developers to create new instances of the Logger or load them
 * from config files (see Phalcon\Config\Config object).
 */
class Logger extends AbstractLogger implements LoggerInterface
{
    use LoggerTrait;

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed             $level
     * @param string|Stringable $message
     * @param array             $context
     *
     * @return void
     * @throws Exception
     */
    public function log(
        mixed $level,
        string | Stringable $message,
        array $context = []
    ): void {
        $levelName = $this->getLevelNumber($level);

        $this->addMessage($levelName, $message, $context);
    }
}

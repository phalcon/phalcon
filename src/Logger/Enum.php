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

/**
 * Log Level Enum constants
 */
class Enum
{
    public const ALERT     = 2;
    public const CRITICAL  = 1;
    /**
     * Default threshold and fallback sink. It sits between DEBUG (7) and
     * TRACE (9) in the ordering, so the default log level excludes TRACE.
     * It is also the fallback for unknown message levels and invalid
     * setLogLevel() values.
     */
    public const CUSTOM    = 8;
    public const DEBUG     = 7;
    public const EMERGENCY = 0;
    public const ERROR     = 3;
    public const INFO      = 6;
    public const NOTICE    = 5;
    public const TRACE     = 9;
    public const WARNING   = 4;
}

<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Implementation of this file has been influenced by lcobucci/clock
 *
 * @link    https://github.com/lcobucci/clock
 * @license https://github.com/lcobucci/clock/blob/3.7.x/LICENSE
 */

declare(strict_types=1);

namespace Phalcon\Time\Clock;

use DateTimeImmutable;
use DateTimeZone;

use function date_default_timezone_get;

final class SystemClock implements ClockInterface
{
    /**
     * @var DateTimeZone
     */
    private DateTimeZone $timezone;

    public function __construct(DateTimeZone $timezone)
    {
        $this->timezone = $timezone;
    }

    /**
     * Return a new object of now with the current timezone
     */
    public static function fromSystemTimezone(): SystemClock
    {
        return new SystemClock(new DateTimeZone(date_default_timezone_get()));
    }

    /**
     * Return a new object of now with UTC
     */
    public static function fromUTC(): SystemClock
    {
        return new SystemClock(new DateTimeZone('UTC'));
    }

    /**
     * Return the current clock
     */
    public function now(): DateTimeImmutable
    {
        return new DateTimeImmutable('now', $this->timezone);
    }
}

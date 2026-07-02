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
use Phalcon\Time\Clock\Exceptions\InvalidModifier;
use Throwable;

use function date_default_timezone_get;
use function restore_error_handler;
use function set_error_handler;

use const E_WARNING;
use const PHP_VERSION_ID;

final class FrozenClock implements ClockInterface
{
    /**
     * @var DateTimeImmutable
     */
    private DateTimeImmutable $now;

    public function __construct(DateTimeImmutable $now)
    {
        $this->now = $now;
    }

    /**
     * Return a new object of now with the current timezone
     */
    public static function fromSystemTimezone(): FrozenClock
    {
        return new FrozenClock(
            new DateTimeImmutable(
                'now',
                new DateTimeZone(date_default_timezone_get())
            )
        );
    }

    /**
     * Return a new object of now with UTC
     */
    public static function fromUTC(): FrozenClock
    {
        return new FrozenClock(
            new DateTimeImmutable('now', new DateTimeZone('UTC'))
        );
    }

    /**
     * Mutates the clock to a new value. All consumers receive the same modification
     *
     * @throws InvalidModifier
     */
    public function adjust(string $modifier): static
    {
        if (PHP_VERSION_ID >= 80300) {
            try {
                $modified = $this->now->modify($modifier);
            } catch (Throwable $ex) {
                throw new InvalidModifier($modifier, $ex);
            }
        } else {
            $failed = false;
            set_error_handler(
                static function () use (&$failed): bool {
                    $failed = true;

                    return true;
                },
                E_WARNING
            );

            try {
                $modified = $this->now->modify($modifier);
            } finally {
                restore_error_handler();
            }

            if ($failed) {
                throw new InvalidModifier($modifier);
            }
        }

        if (false === $modified) {
            throw new InvalidModifier($modifier);
        }

        $this->now = $modified;

        return $this;
    }

    /**
     * Return the current clock
     */
    public function now(): DateTimeImmutable
    {
        return $this->now;
    }

    /**
     * Sets the clock to a new value. All consumers receive the same modification
     */
    public function set(DateTimeImmutable $now): static
    {
        $this->now = $now;

        return $this;
    }
}

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

namespace Phalcon\Db\Traits;

/**
 * Derives elapsed milliseconds and seconds from the nanosecond total that the
 * using class exposes through getTotalElapsedNanoseconds().
 */
trait ElapsedTimeTrait
{
    /**
     * Returns the total time in milliseconds spent by the profiles
     */
    public function getTotalElapsedMilliseconds(): float
    {
        return $this->getTotalElapsedNanoseconds() / 1000000;
    }
    /**
     * Returns the total time in nanoseconds spent by the profiles. Implemented
     * by the using class.
     */
    abstract public function getTotalElapsedNanoseconds(): float;

    /**
     * Returns the total time in seconds spent by the profiles
     */
    public function getTotalElapsedSeconds(): float
    {
        return $this->getTotalElapsedMilliseconds() / 1000;
    }
}

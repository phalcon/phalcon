<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Implementation of this file has been influenced by sinbadxiii/cphalcon-uuid
 * @link    https://github.com/sinbadxiii/cphalcon-uuid
 */

declare(strict_types=1);

namespace Phalcon\Encryption\Security\Uuid;

use DateTimeImmutable;
use DateTimeInterface;

/**
 * Generates a version 1 (time-based) UUID.
 *
 * The timestamp is the number of 100-nanosecond intervals since
 * October 15, 1582 00:00:00.00 UTC (the UUID epoch). The node is resolved
 * via SysNodeProvider (hardware MAC, APCu-cached) with RandomNodeProvider
 * as fallback.
 *
 * @link https://www.ietf.org/rfc/rfc4122.txt
 */
class Version1 extends AbstractUuid implements TimeBasedUuidInterface
{
    public function __construct(
        DateTimeInterface | null $dateTime = null,
        mixed $node = null
    ) {
        if ($dateTime !== null) {
            $dateTimestamp = $dateTime->getTimestamp();
            $sec           = $dateTimestamp;
            $dateUsec      = intval($dateTime->format("u")) * 10;
            $usec          = $dateUsec;
        } else {
            $nowSec  = time();
            $sec     = $nowSec;
            $nowUsec = intval(round((microtime(true) - doubleval($nowSec)) * 10000000.0));
            $usec    = $nowUsec;
        }

        $timestamp = ($sec + 12219292800) * 10000000 + $usec;

        $timeLow = $timestamp & 0xffffffff;
        $timeMid = ($timestamp >> 32) & 0xffff;
        $timeHi  = (($timestamp >> 48) & 0x0fff) | 0x1000;

        $clockSeqBytes = random_bytes(2);
        $clockSeqHiRes = (ord(substr($clockSeqBytes, 0, 1)) & 0x3f) | 0x80;
        $clockSeqLow   = ord(substr($clockSeqBytes, 1, 1));

        if ($node !== null) {
            $nodeStr = $node;
        } else {
            $nodeStr = $this->getNodeProvider()->getNode();
        }

        $this->uid = sprintf(
            "%08x-%04x-%04x-%02x%02x-%s",
            $timeLow,
            $timeMid,
            $timeHi,
            $clockSeqHiRes,
            $clockSeqLow,
            $nodeStr
        );
    }

    /**
     * Returns a DateTimeImmutable built from the UUID's embedded timestamp.
     */
    public function getDateTime(): DateTimeImmutable
    {
        $parts      = explode("-", $this->uid);
        $hexTimeLow = hexdec($parts[0]);
        $hexTimeMid = hexdec($parts[1]);
        $hexTimeHi  = hexdec($parts[2]) & 0x0fff;
        $timeLow    = $hexTimeLow;
        $timeMid    = $hexTimeMid;
        $timeHi     = $hexTimeHi;
        $timestamp  = ($timeHi << 48) | ($timeMid << 32) | $timeLow;

        return $this->uuidTimestampToDateTime($timestamp);
    }

    /**
     * Returns the 12-character hex node embedded in the UUID.
     */
    public function getNode(): string
    {
        return substr($this->uid, 24);
    }
}

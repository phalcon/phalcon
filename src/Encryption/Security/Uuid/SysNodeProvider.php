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

use Phalcon\Traits\Php\FileTrait;
use Phalcon\Traits\Php\InfoTrait;

/**
 * Discovers the hardware MAC address and returns it as a 12-character hex node.
 *
 * Two-layer cache:
 *   1. Instance property  - free on all calls after the first within this instance.
 *   2. APCu               - cross-request within the same PHP-FPM worker (optional).
 *
 * Falls back to RandomNodeProvider if no valid MAC address is found.
 *
 * Platform support:
 *   Linux   - reads /sys/class/net/*\/address
 *   macOS   - passthru("ifconfig 2>&1")
 *   Windows - passthru("ipconfig /all 2>&1")
 *   FreeBSD - passthru("netstat -i -f link 2>&1")
 */
class SysNodeProvider implements NodeProviderInterface
{
    use FileTrait;
    use InfoTrait;

    /**
     * @var string|null
     */
    private string | null $node = null;

    /**
     * Returns the hardware MAC address as a 12-character hex string.
     * Result is cached in the instance property and optionally in APCu.
     */
    public function getNode(): string
    {
        if ($this->node !== null) {
            return $this->node;
        }

        if ($this->phpFunctionExists("apcu_fetch")) {
            $cached = apcu_fetch("__phalcon_uuid_node");
            if ($cached !== false) {
                $this->node = $cached;

                return $this->node;
            }
        }

        $node = null;

        // Linux: read from sysfs
        if (PHP_OS_FAMILY === "Linux") {
            $addresses = glob("/sys/class/net/*/address");

            if (is_array($addresses)) {
                foreach ($addresses as $address) {
                    if (str_contains($address, "/lo/")) {
                        continue;
                    }

                    $node = trim($this->phpFileGetContents($address));
                    $node = str_replace(":", "", $node);

                    if ($this->isValidNode($node)) {
                        break;
                    }

                    $node = null;
                }
            }
        }

        // macOS
        if ($node === null && PHP_OS_FAMILY === "Darwin") {
            ob_start();
            passthru("ifconfig 2>&1");
            $output = ob_get_clean();

            if (
                preg_match(
                    "/ether\\s+([0-9a-f]{2}:[0-9a-f]{2}:[0-9a-f]{2}:[0-9a-f]{2}:[0-9a-f]{2}:[0-9a-f]{2})/i",
                    $output,
                    $matches
                )
            ) {
                $node = str_replace(":", "", $matches[1]);
            }
        }

        // Windows
        if ($node === null && PHP_OS_FAMILY === "Windows") {
            ob_start();
            passthru("ipconfig /all 2>&1");
            $output = ob_get_clean();

            if (
                preg_match(
                    "/"                   // pattern delimiter (open)
                    . "Physical Address"  // literal label text
                    . "[^:]*"             // any characters except a colon
                    . ":"                 // the colon after the label
                    . "\\s+"              // one or more whitespace characters
                    . "("                 // start of the MAC capture group
                    . "[0-9a-f]{2}-"      // MAC octet 1 (two hex digits + dash)
                    . "[0-9a-f]{2}-"      // MAC octet 2
                    . "[0-9a-f]{2}-"      // MAC octet 3
                    . "[0-9a-f]{2}-"      // MAC octet 4
                    . "[0-9a-f]{2}-"      // MAC octet 5
                    . "[0-9a-f]{2}"       // MAC octet 6 (no trailing dash)
                    . ")"                 // end of the capture group
                    . "/i",               // delimiter (close) + case-insensitive
                    $output,
                    $matches
                )
            ) {
                $node = strtolower(str_replace("-", "", $matches[1]));
            }
        }

        // FreeBSD
        if ($node === null && PHP_OS_FAMILY === "BSD") {
            ob_start();
            passthru("netstat -i -f link 2>&1");
            $output = ob_get_clean();

            if (
                    preg_match(
                        "/([0-9a-f]{2}:[0-9a-f]{2}:[0-9a-f]{2}:[0-9a-f]{2}:[0-9a-f]{2}:[0-9a-f]{2})/i",
                        $output,
                        $matches
                    )
            ) {
                $node = str_replace(":", "", $matches[1]);
            }
        }

        if ($node === null || !$this->isValidNode($node)) {
            $node = (new RandomNodeProvider())->getNode();
        }

        $this->node = $node;

        if ($this->phpFunctionExists("apcu_store")) {
            apcu_store("__phalcon_uuid_node", $this->node);
        }

        return $this->node;
    }

    /**
     * Returns true if the given hex node is a valid non-loopback, non-broadcast MAC.
     */
    private function isValidNode(string $node): bool
    {
        if (strlen($node) !== 12) {
            return false;
        }

        if (!ctype_xdigit($node)) {
            return false;
        }

        if ($node === "000000000000" || $node === "ffffffffffff") {
            return false;
        }

        return true;
    }
}

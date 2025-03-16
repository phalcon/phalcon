<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Html\Helper;

use const PHP_EOL;

/**
 * Creates Doctype tags
 */
class Doctype
{
    public const HTML32               = 1;
    public const HTML401_FRAMESET     = 4;
    public const HTML401_STRICT       = 2;
    public const HTML401_TRANSITIONAL = 3;
    public const HTML5                = 5;
    public const XHTML10_FRAMESET     = 8;
    public const XHTML10_STRICT       = 6;
    public const XHTML10_TRANSITIONAL = 7;
    public const XHTML11              = 9;
    public const XHTML20              = 10;
    public const XHTML5               = 11;

    /**
     * @var string
     */
    private string $delimiter = PHP_EOL;

    /**
     * @var int
     */
    private int $flag = self::HTML5;

    /**
     * Produce a <doctype> tag
     *
     * @param int    $flag
     * @param string $delimiter
     *
     * @return static
     */
    public function __invoke(
        int $flag = self::HTML5,
        string $delimiter = PHP_EOL
    ): static {
        $this->flag      = $flag;
        $this->delimiter = $delimiter;

        return $this;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        $prefix = "<!DOCTYPE html PUBLIC \"-//W3C//DTD ";
        $dlm    = $this->delimiter;
        $map    = [
            self::HTML32               => $prefix . "HTML 3.2 Final//EN\">" . $dlm,
            self::HTML401_STRICT       => $prefix . "HTML 4.01//EN\"" . $dlm
                . "\t\"http://www.w3.org/TR/html4/strict.dtd\">" . $dlm,
            self::HTML401_TRANSITIONAL => $prefix . "HTML 4.01 Transitional//EN\"" . $dlm
                . "\t\"http://www.w3.org/TR/html4/loose.dtd\">" . $dlm,
            self::HTML401_FRAMESET     => $prefix . "HTML 4.01 Frameset//EN\"" . $dlm
                . "\t\"http://www.w3.org/TR/html4/frameset.dtd\">" . $dlm,
            self::XHTML10_STRICT       => $prefix . "XHTML 1.0 Strict//EN\"" . $dlm
                . "\t\"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">" . $dlm,
            self::XHTML10_TRANSITIONAL => $prefix . "XHTML 1.0 Transitional//EN\"" . $dlm
                . "\t\"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">" . $dlm,
            self::XHTML10_FRAMESET     => $prefix . "XHTML 1.0 Frameset//EN\"" . $dlm
                . "\t\"http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd\">" . $dlm,
            self::XHTML11              => $prefix . "XHTML 1.1//EN\"" . $dlm
                . "\t\"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd\">" . $dlm,
            self::XHTML20              => $prefix . "XHTML 2.0//EN\"" . $dlm
                . "\t\"http://www.w3.org/MarkUp/DTD/xhtml2.dtd\">" . $dlm,
        ];

        /**
         * Default is HTML5
         */
        return $map[$this->flag] ?? "<!DOCTYPE html>" . $dlm;
    }
}

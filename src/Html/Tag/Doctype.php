<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Html\Tag;

use const PHP_EOL;

/**
 * Class Doctype
 *
 * @package Phalcon\Html\Tag
 *
 * @property string $documentType
 */
class Doctype extends AbstractHelper
{
    const HTML32 = 1;
    const HTML401_STRICT = 2;
    const HTML401_TRANSITIONAL = 3;
    const HTML401_FRAMESET = 4;
    const HTML5 = 5;
    const XHTML10_STRICT = 6;
    const XHTML10_TRANSITIONAL = 7;
    const XHTML10_FRAMESET = 8;
    const XHTML11 = 9;
    const XHTML20 = 10;
    const XHTML5 = 11;
    /**
     * @var int
     */
    protected int $documentType = 11;

    /**
     * Returns the doctype
     *
     * @return string
     * @throws Exception
     */
    public function __toString()
    {
        switch ($this->documentType)
        {
            case 1:  return "<!DOCTYPE html PUBLIC \"-//W3C//DTD HTML 3.2 Final//EN\">" . PHP_EOL;
            /* no break */

            case 2:  return "<!DOCTYPE html PUBLIC \"-//W3C//DTD HTML 4.01//EN\"" . PHP_EOL . "\t\"http://www.w3.org/TR/html4/strict.dtd\">" . PHP_EOL;
            /* no break */

            case 3:  return "<!DOCTYPE html PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\"" . PHP_EOL . "\t\"http://www.w3.org/TR/html4/loose.dtd\">" . PHP_EOL;
            /* no break */

            case 4:  return "<!DOCTYPE html PUBLIC \"-//W3C//DTD HTML 4.01 Frameset//EN\"" . PHP_EOL . "\t\"http://www.w3.org/TR/html4/frameset.dtd\">" . PHP_EOL;
            /* no break */

            case 6:  return "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\"" . PHP_EOL . "\t\"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">" . PHP_EOL;
            /* no break */

            case 7:  return "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\"" . PHP_EOL."\t\"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">" . PHP_EOL;
            /* no break */

            case 8:  return "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Frameset//EN\"" . PHP_EOL . "\t\"http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd\">" . PHP_EOL;
            /* no break */

            case 9:  return "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.1//EN\"" . PHP_EOL . "\t\"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd\">" . PHP_EOL;
            /* no break */

            case 10: return "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 2.0//EN\"" . PHP_EOL . "\t\"http://www.w3.org/MarkUp/DTD/xhtml2.dtd\">" . PHP_EOL;
            /* no break */

            case 5:
            case 11: return "<!DOCTYPE html>" . PHP_EOL;
            /* no break */
        }

        return "";
    }

    /**
     * Returns the title
     *
     * @return string
     */
    public function get(): string
    {
        return $this->documentType;
    }

    /**
     * Sets the Doctype
     *
     * @param int $doctype
     *      *
     * @return Doctype
     */
    public function set(int $doctype): Doctype
    {
        if (($doctype < self::HTML32) || ($doctype > self::XHTML5)) {
            $this->documentType = self::HTML5;
        } else {
            $this->documentType = $doctype;
        }

        return $this;
    }
}

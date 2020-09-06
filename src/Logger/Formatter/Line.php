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

namespace Phalcon\Logger\Formatter;

use Exception;
use Phalcon\Helper\Str;
use Phalcon\Logger\Item;

use function str_replace;
use function var_dump;

/**
 * Class Line
 *
 * @property string $format
 */
class Line extends AbstractFormatter
{
    /**
     * Format applied to each message
     *
     * @var string
     */
    protected string $format;

    /**
     * Line constructor.
     *
     * @param string $format
     * @param string $dateFormat
     */
    public function __construct(
        string $format = "[{date}][{type}] {message}",
        string $dateFormat = "c"
    ) {
        parent::__construct();

        $this->format     = $format;
        $this->dateFormat = $dateFormat;
    }

    /**
     * Applies a format to a message before sent it to the internal log
     *
     * @param Item $item
     *
     * @return string
     * @throws Exception
     */
    public function format(Item $item): string
    {
        $format = strtr(
            $this->format,
            [
                '{date}'    => $this->getFormattedDate($item),
                '{type}'    => $item->getName(),
                '{message}' => $item->getMessage(),
            ]
        );

        var_dump($item);
        var_dump($this->format);
        var_dump($this->dateFormat);
        var_dump($this->getDateFormat());
        var_dump($format);

        return $this->interpolate(
            $format,
            $item->getContext()
        );
    }

    /**
     * @return string
     */
    public function getFormat(): string
    {
        return $this->format;
    }

    /**
     * @param string $format
     */
    public function setFormat(string $format): void
    {
        $this->format = $format;
    }
}

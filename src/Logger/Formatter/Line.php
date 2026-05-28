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
use Phalcon\Logger\Item;

/**
 * Class Line
 *
 * @property string $format
 */
class Line extends AbstractFormatter
{
    /**
     * Line constructor.
     *
     * @param string $format
     * @param string $dateFormat
     * @param string $interpolatorLeft
     * @param string $interpolatorRight
     */
    public function __construct(
        protected string $format = '[%date%][%level%] %message%',
        string $dateFormat = 'c',
        string $interpolatorLeft = '%',
        string $interpolatorRight = '%'
    ) {
        $this->dateFormat        = $dateFormat;
        $this->interpolatorLeft  = $interpolatorLeft;
        $this->interpolatorRight = $interpolatorRight;
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
        $message = strtr(
            $this->format,
            [
                $this->interpolatorLeft . 'date'    . $this->interpolatorRight => $this->getFormattedDate($item),
                $this->interpolatorLeft . 'level'   . $this->interpolatorRight => $item->getLevelName(),
                $this->interpolatorLeft . 'message' . $this->interpolatorRight => $item->getMessage(),
            ]
        );

        return $this->getInterpolatedMessage($item, $message);
    }

    /**
     * Return the format applied to each message
     *
     * @return string
     */
    public function getFormat(): string
    {
        return $this->format;
    }

    /**
     * Set the format applied to each message
     *
     * @param string $format
     *
     * @return Line
     */
    public function setFormat(string $format): static
    {
        $this->format = $format;

        return $this;
    }
}

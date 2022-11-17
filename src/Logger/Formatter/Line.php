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
     */
    public function __construct(
        protected string $format = '[%date%][%level%] %message%',
        string $dateFormat = 'c'
    ) {
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
        $message = strtr(
            $this->format,
            [
                '%date%'    => $this->getFormattedDate($item),
                '%level%'   => $item->getLevelName(),
                '%message%' => $item->getMessage(),
            ]
        );

        return $this->toInterpolate($message, $item->getContext());
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
    public function setFormat(string $format): Line
    {
        $this->format = $format;

        return $this;
    }
}

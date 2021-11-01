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

use Phalcon\Logger\Item;
use Phalcon\Traits\Helper\Str\InterpolateTrait;

/**
 * Class AbstractFormatter
 *
 * @property string $dateFormat
 */
abstract class AbstractFormatter implements FormatterInterface
{
    use InterpolateTrait;

    /**
     * Default date format
     *
     * @var string
     */
    protected string $dateFormat = 'c';

    /**
     * @return string
     */
    public function getDateFormat(): string
    {
        return $this->dateFormat;
    }

    /**
     * @param string $format
     */
    public function setDateFormat(string $format): void
    {
        $this->dateFormat = $format;
    }

    /**
     * Returns the date formatted for the logger.
     *
     * @param Item $item
     *
     * @return string
     */
    protected function getFormattedDate(Item $item): string
    {
        return $item->getDateTime()
                    ->format($this->dateFormat)
        ;
    }
}

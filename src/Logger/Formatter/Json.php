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
use Phalcon\Helper\Json as JsonHelper;
use Phalcon\Logger\Item;

/**
 * Phalcon\Logger\Formatter\Json
 *
 * Formats messages using JSON encoding
 */
class Json extends AbstractFormatter
{
    /**
     * Json constructor.
     *
     * @param string $dateFormat
     */
    public function __construct(string $dateFormat = "c")
    {
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
        $message = $this->interpolate(
            $item->getMessage(),
            $item->getContext()
        );

        return JsonHelper::encode(
            [
                "type"      => $item->getName(),
                "message"   => $message,
                "timestamp" => $this->getFormattedDate(),
            ]
        );
    }
}

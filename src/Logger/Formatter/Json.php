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

use JsonException;
use Phalcon\Logger\Item;

use function json_encode;

use const JSON_HEX_AMP;
use const JSON_HEX_APOS;
use const JSON_HEX_QUOT;
use const JSON_HEX_TAG;
use const JSON_THROW_ON_ERROR;
use const JSON_UNESCAPED_SLASHES;

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
    public function __construct(string $dateFormat = 'c')
    {
        $this->dateFormat = $dateFormat;
    }

    /**
     * Applies a format to a message before sent it to the internal log
     *
     * @param Item $item
     *
     * @return string
     * @throws JsonException
     */
    public function format(Item $item): string
    {
        $options = JSON_HEX_TAG + JSON_HEX_APOS + JSON_HEX_AMP
                 + JSON_HEX_QUOT + JSON_UNESCAPED_SLASHES
                 + JSON_THROW_ON_ERROR;
        $message = $this->interpolate(
            $item->getMessage(),
            $item->getContext()
        );

        return json_encode(
            [
                'type'      => $item->getName(),
                'message'   => $message,
                'timestamp' => $this->getFormattedDate($item),
            ],
            $options
        );
    }
}

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

use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use Exception;
use Phalcon\Logger\Item;

use function date_default_timezone_get;

/**
 * Class AbstractFormatter
 *
 * @property string $dateFormat
 */
abstract class AbstractFormatter implements FormatterInterface
{
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
     * Interpolates context values into the message placeholders
     *
     * @see http://www.php-fig.org/psr/psr-3/ Section 1.2 Message
     *
     * @param string $message
     * @param array  $context
     *
     * @return string
     */
    public function interpolate(string $message, array $context = [])
    {
        if (empty($context)) {
            return $message;
        }

        $replace = [];
        foreach ($context as $key => $value) {
            $replace["{" . $key . "}"] = $value;
        }

        return strtr($message, $replace);
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
     * @return string
     * @throws Exception
     * @todo Not using the set time from the Item since we have interface
     *       misalignment which will break semver This will change in the future
     *
     */
    protected function getFormattedDate(): string
    {
        $timezone = date_default_timezone_get();
        $date     = new DateTimeImmutable("now", new DateTimeZone($timezone));

        return $date->format($this->dateFormat);
    }
}

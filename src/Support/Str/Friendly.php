<?php

/**
 * This file is part of the Phalcon.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Support\Str;

use Phalcon\Support\Exception;
use Phalcon\Support\Str\Traits\LowerTrait;

use function is_array;
use function is_string;
use function preg_replace;
use function str_replace;
use function trim;

/**
 * Class Friendly
 *
 * @package Phalcon\Support\Str
 */
class Friendly
{
    use LowerTrait;

    /**
     * Changes a text to a URL friendly one
     *
     * @param string     $text
     * @param string     $separator
     * @param bool       $lowercase
     * @param mixed|null $replace
     *
     * @return string
     * @throws Exception
     */
    public function __invoke(
        string $text,
        string $separator = '-',
        bool $lowercase = true,
        $replace = null
    ): string {
        if (null !== $replace) {
            $text = $this->checkReplace($replace, $text);
        }

        $friendly = preg_replace(
            '/[^a-zA-Z0-9\\/_|+ -]/',
            '',
            $text
        );

        if ($lowercase) {
            $friendly = $this->toLower($friendly);
        }

        return trim(
            (string) preg_replace(
                '/[\\/_|+ -]+/',
                $separator,
                $friendly
            ),
            $separator
        );
    }

    /**
     * @param array|string $replace
     * @param string       $text
     *
     * @return string
     * @throws Exception
     */
    private function checkReplace($replace, string $text): string
    {
        if (true !== is_array($replace) && true !== is_string($replace)) {
            throw new Exception(
                'Parameter replace must be an array or a string'
            );
        }

        if (true === is_string($replace)) {
            $replace = [$replace];
        }

        return str_replace($replace, ' ', $text);
    }
}

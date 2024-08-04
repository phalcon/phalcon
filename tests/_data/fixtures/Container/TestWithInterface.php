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

namespace Phalcon\Tests\Fixtures\Container;

use stdClass;

class TestWithInterface extends stdClass implements TestInterface
{
    /**
     * @param string $one
     * @param string $two
     */
    public function __construct(
        public string $one,
        public string $two = 'two'
    ) {
    }

    /**
     * @param string $suffix
     *
     * @return void
     */
    public function append(string $suffix) : void
    {
        $this->one .= $suffix;
    }

    /**
     * @return string
     */
    public function getValue() : string
    {
        return $this->two;
    }

    public static function staticMethod(string $word) : string
    {
        return $word;
    }
}

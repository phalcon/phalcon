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

namespace Phalcon\Flash;

/**
 * Class Direct
 *
 * @package Phalcon\Flash
 */
class Direct extends AbstractFlash
{
    /**
     * Outputs a message
     *
     * @param string $type
     * @param mixed  $message
     *
     * @return string|null
     * @throws Exception
     */
    public function message(string $type, $message): string | null
    {
        return $this->outputMessage($type, $message);
    }

    /**
     * Prints the messages accumulated in the flasher
     */
    public function output(bool $remove = true): void
    {
        foreach ($this->messages as $message) {
            echo $message;
        }

        if (true === $remove) {
            parent::clear();
        }
    }
}

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

namespace Phalcon\Encryption\Security\JWT\Token;

/**
 * Abstract helper class for Tokens
 *
 * @property array $data
 */
abstract class AbstractItem
{
    /**
     * @var array
     */
    protected array $data = [];

    /**
     * @return string
     */
    public function getEncoded(): string
    {
        return $this->data['encoded'];
    }
}

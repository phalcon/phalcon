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

namespace Phalcon\Domain;

use PayloadInterop\DomainPayload;

/**
 * Factory to create payload objects
 *
 * @package Phalcon\Domain
 */
class PayloadFactory
{
    /**
     * Instantiate a new object
     *
     * @param string $status
     * @param array  $result
     *
     * @return DomainPayload
     */
    public function newInstance(string $status, array $result = []): DomainPayload
    {
        return new Payload($status, $result);
    }
}

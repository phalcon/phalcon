<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Implementation of this file has been influenced by cardoe-api and AuraPHP
 *
 * @link    https://github.com/cardoe/cardoe-api
 * @license https://github.com/cardoe/cardoe-api/blob/master/LICENSE
 * @link    https://github.com/auraphp/Aura.Payload
 * @license https://github.com/auraphp/Aura.Payload/blob/3.x/LICENSE
 *
 * @see     Original inspiration for the https://github.com/cardoe/cardoe-api
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

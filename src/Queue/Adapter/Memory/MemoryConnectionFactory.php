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

namespace Phalcon\Queue\Adapter\Memory;

use Phalcon\Contracts\Queue\ConnectionFactory as ConnectionFactoryInterface;
use Phalcon\Contracts\Queue\Context as ContextInterface;

/**
 * Builds a MemoryContext. The Memory transport takes no options.
 */
class MemoryConnectionFactory implements ConnectionFactoryInterface
{
    public function __construct(array $options = [])
    {
    }

    public function createContext(): ContextInterface
    {
        return new MemoryContext();
    }
}

<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Implementation of this component has been inspired by the queue-interop and
 * enqueue projects.
 *
 * @link    https://github.com/queue-interop/queue-interop
 * @license https://github.com/queue-interop/queue-interop/blob/master/LICENSE
 *
 * @link    https://github.com/php-enqueue/enqueue-dev
 * @license https://github.com/php-enqueue/enqueue-dev/blob/master/LICENSE
 */

declare(strict_types=1);

namespace Phalcon\Queue\Adapter\Memory;

use Phalcon\Contracts\Queue\ConnectionFactory as ConnectionFactoryInterface;
use Phalcon\Contracts\Queue\Context as ContextInterface;
use Phalcon\Contracts\Queue\QueueTypes;

/**
 * Builds a MemoryContext. The Memory transport takes no options.
 *
 * @phpstan-import-type queue_connection_options from QueueTypes
 */
class MemoryConnectionFactory implements ConnectionFactoryInterface
{
    /**
     * MemoryConnectionFactory constructor.
     *
     * @phpstan-param queue_connection_options $options
     */
    public function __construct(protected array $options = [])
    {
    }

    /**
     * Creates a new in-process context.
     */
    public function createContext(): ContextInterface
    {
        return new MemoryContext();
    }
}

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

namespace Phalcon\Queue\Adapter\Stream;

use Phalcon\Contracts\Queue\ConnectionFactory as ConnectionFactoryInterface;
use Phalcon\Contracts\Queue\Context as ContextInterface;

use function sys_get_temp_dir;

/**
 * Builds a StreamContext.
 *
 * Options:
 *   - storageDir:   directory holding the queue files (default: system temp).
 *   - pollInterval: milliseconds between consumer poll attempts (default 200).
 */
class StreamConnectionFactory implements ConnectionFactoryInterface
{
    public function __construct(protected array $options = [])
    {
    }

    public function createContext(): ContextInterface
    {
        $storageDir = $this->options["storageDir"] ?? sys_get_temp_dir();
        $pollInterval = (int) ($this->options["pollInterval"] ?? 200);

        return new StreamContext($storageDir, $pollInterval);
    }
}

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

namespace Phalcon\Queue\Adapter\Beanstalk;

use Phalcon\Queue\Adapter\AbstractMessage;

/**
 * Beanstalkd-backed message. Carries the reserved job id so the consumer can
 * delete, release, bury or touch it; all other behavior comes from
 * AbstractMessage.
 */
class BeanstalkMessage extends AbstractMessage
{
    protected ?string $jobId = null;

    public function getJobId(): ?string
    {
        return $this->jobId;
    }

    public function setJobId(string $jobId): void
    {
        $this->jobId = $jobId;
    }
}

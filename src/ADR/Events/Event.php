<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Based on the Action Domain Responder pattern
 * @link    https://pmjones.io/adr/
 */

declare(strict_types=1);

namespace Phalcon\ADR\Events;

/**
 * The ADR event vocabulary, fired through the native events manager.
 */
class Event
{
    /**
     * @var string
     */
    public const ADR_AFTER_EXECUTE_ACTION  = 'adr:afterExecuteAction';
    /**
     * @var string
     */
    public const ADR_BEFORE_EXECUTE_ACTION = 'adr:beforeExecuteAction';
    /**
     * @var string
     */
    public const APPLICATION_AFTER_HANDLE  = 'application:afterHandle';
    /**
     * @var string
     */
    public const APPLICATION_BEFORE_HANDLE = 'application:beforeHandle';
    /**
     * @var string
     */
    public const PIPELINE_AFTER_DISPATCH   = 'pipeline:afterDispatch';
    /**
     * @var string
     */
    public const PIPELINE_BEFORE_DISPATCH  = 'pipeline:beforeDispatch';

    /**
     * Instantiation not allowed.
     */
    final private function __construct()
    {
    }
}

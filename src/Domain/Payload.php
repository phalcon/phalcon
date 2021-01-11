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
 * Class Payload
 *
 * @package Phalcon\Domain
 *
 * @property array  $result
 * @property string $status
 */
class Payload implements DomainPayload
{
    /**
     * @var array
     */
    protected array $result = [];

    /**
     * @var string
     */
    protected string $status;

    /**
     * Payload constructor.
     *
     * @param string $status
     * @param array  $result
     */
    public function __construct(string $status, array $result = [])
    {
        $this->result = $result;
        $this->status = $status;
    }

    /**
     * @return array
     */
    public function getResult(): array
    {
        return $this->result;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }
}

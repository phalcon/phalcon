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

namespace Phalcon\Support\Debug\Report;

/**
 * Represents a single resolved frame of an exception backtrace.
 */
final class BacktraceItem
{
    /**
     * @param string      $functionName
     * @param string|null $type
     * @param string|null $className
     * @param string|null $classLink
     * @param string|null $functionLink
     * @param bool        $hasArgs
     * @param array       $args
     * @param string|null $file
     * @param int|null    $line
     * @param array|null  $fragment
     */
    public function __construct(
        private readonly string $functionName,
        private readonly ?string $type = null,
        private readonly ?string $className = null,
        private readonly ?string $classLink = null,
        private readonly ?string $functionLink = null,
        private readonly bool $hasArgs = false,
        private readonly array $args = [],
        private readonly ?string $file = null,
        private readonly ?int $line = null,
        private readonly ?array $fragment = null,
    ) {
    }

    /**
     * @return array
     */
    public function getArgs(): array
    {
        return $this->args;
    }

    /**
     * @return string|null
     */
    public function getClassLink(): ?string
    {
        return $this->classLink;
    }

    /**
     * @return string|null
     */
    public function getClassName(): ?string
    {
        return $this->className;
    }

    /**
     * @return string|null
     */
    public function getFile(): ?string
    {
        return $this->file;
    }

    /**
     * @return array|null
     */
    public function getFragment(): ?array
    {
        return $this->fragment;
    }

    /**
     * @return string|null
     */
    public function getFunctionLink(): ?string
    {
        return $this->functionLink;
    }

    /**
     * @return string
     */
    public function getFunctionName(): string
    {
        return $this->functionName;
    }

    /**
     * @return int|null
     */
    public function getLine(): ?int
    {
        return $this->line;
    }

    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @return bool
     */
    public function hasArgs(): bool
    {
        return $this->hasArgs;
    }
}

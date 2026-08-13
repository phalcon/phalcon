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

use Phalcon\Contracts\Support\SupportTypes;

/**
 * Represents a single resolved frame of an exception backtrace.
 *
 * @phpstan-import-type support_debug_args from SupportTypes
 * @phpstan-import-type support_debug_fragment from SupportTypes
 */
final class BacktraceItem
{
    /**
     * @phpstan-param support_debug_args          $args
     * @phpstan-param support_debug_fragment|null $fragment
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
     * @phpstan-return support_debug_args
     */
    public function getArgs(): array
    {
        return $this->args;
    }

    public function getClassLink(): ?string
    {
        return $this->classLink;
    }

    public function getClassName(): ?string
    {
        return $this->className;
    }

    public function getFile(): ?string
    {
        return $this->file;
    }

    /**
     * @phpstan-return support_debug_fragment|null
     */
    public function getFragment(): ?array
    {
        return $this->fragment;
    }

    public function getFunctionLink(): ?string
    {
        return $this->functionLink;
    }

    public function getFunctionName(): string
    {
        return $this->functionName;
    }

    public function getLine(): ?int
    {
        return $this->line;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function hasArgs(): bool
    {
        return $this->hasArgs;
    }
}

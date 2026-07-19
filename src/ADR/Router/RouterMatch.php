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

namespace Phalcon\ADR\Router;

use Phalcon\Contracts\ADR\Router\RouterMatch as RouterMatchInterface;

/**
 * Immutable result of a successful route match.
 */
final class RouterMatch implements RouterMatchInterface
{
    /**
     * @var string
     */
    protected string $action;

    /**
     * @var array
     */
    protected array $attributes;

    /**
     * @var array
     */
    protected array $middleware;

    /**
     * @var string|null
     */
    protected string | null $name;

    public function __construct(string $action, array $attributes = [], array $middleware = [], mixed $name = null)
    {
        $this->action     = $action;
        $this->attributes = $attributes;
        $this->middleware = $middleware;
        $this->name       = $name;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getMiddleware(): array
    {
        return $this->middleware;
    }

    public function getName(): ?string
    {
        return $this->name;
    }
}

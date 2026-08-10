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
 *
 * Implementation of this file has also been heavily influenced by Autoroute.
 *
 * @link    https://pmjones.io/adr/
 *
 * @link    https://github.com/pmjones/AutoRoute
 * @license https://github.com/pmjones/AutoRoute/blob/2.x/LICENSE.md
 */

declare(strict_types=1);

namespace Phalcon\ADR\Router;

use Phalcon\ADR\Exceptions\RouteNotFound;
use Phalcon\Contracts\ADR\ADRTypes;
use Phalcon\Contracts\ADR\Router\AttributeFilter as AttributeFilterInterface;

use function call_user_func;
use function is_array;
use function method_exists;
use function preg_match;

/**
 * Reads an Action's optional static `params()` declaration and transforms the
 * router's positional tail segments: regex match (miss => RouteNotFound), cast
 * to a scalar type, then an optional converter closure. Declaration order names
 * the attributes; a declared parameter with no segment is skipped; surplus
 * segments pass through under their positional keys. An Action without
 * `params()` is returned unchanged.
 *
 * @phpstan-import-type adr_action_params from ADRTypes
 * @phpstan-import-type adr_filtered_attributes from ADRTypes
 * @phpstan-import-type adr_route_attributes from ADRTypes
 */
final class AttributeFilter implements AttributeFilterInterface
{
    /**
     * @phpstan-param class-string          $actionClass
     * @phpstan-param adr_route_attributes  $attributes
     *
     * @phpstan-return adr_filtered_attributes
     */
    public function filter(string $actionClass, array $attributes): array
    {
        if (!method_exists($actionClass, 'params')) {
            return $attributes;
        }

        /** @var callable(): mixed $callback */
        $callback = [$actionClass, 'params'];

        $declared = $callback();
        if (!is_array($declared)) {
            return $attributes;
        }

        /** @phpstan-var adr_action_params $params */
        $params = $declared;

        $result = [];
        $index  = 0;

        foreach ($params as $name => $rule) {
            if (isset($attributes[$index])) {
                $segment = $attributes[$index];

                if (
                    isset($rule['match'])
                    && !preg_match('#^(?:' . $rule['match'] . ')$#', $segment)
                ) {
                    throw new RouteNotFound();
                }

                $value = $this->cast($segment, $rule['type'] ?? 'string');

                if (isset($rule['convert'])) {
                    $value = call_user_func($rule['convert'], $value);
                }

                $result[$name] = $value;
            }

            $index++;
        }

        foreach ($attributes as $key => $item) {
            if ($key >= $index) {
                $result[$key] = $item;
            }
        }

        return $result;
    }

    protected function cast(string $value, string $type): float | int | string
    {
        return match ($type) {
            'int'   => (int) $value,
            'float' => (float) $value,
            default => (string) $value,
        };
    }
}

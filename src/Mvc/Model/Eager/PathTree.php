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

namespace Phalcon\Mvc\Model\Eager;

use Phalcon\Contracts\Mvc\MvcTypes;
use Phalcon\Mvc\Model\Exceptions\InvalidEagerPath;
use Phalcon\Mvc\Model\Exceptions\UnsupportedEagerOption;

use function array_merge;
use function count;
use function explode;
use function get_class;
use function is_array;
use function is_object;
use function is_string;
use function trim;

/**
 * Turns the `eager` find parameter into a tree.
 *
 * Elements are either a bare path string or `path => options`. A path implies
 * every one of its prefixes and prefixes are merged, so ["customer",
 * "customer.country"] and ["customer.country"] produce the same two-node tree.
 * The number of queries an eager load costs follows the number of nodes in
 * this tree, not the number of elements supplied.
 *
 * @phpstan-import-type mvc_eager_node from MvcTypes
 * @phpstan-import-type mvc_eager_tree from MvcTypes
 * @phpstan-import-type mvc_model_parameters from MvcTypes
 */
class PathTree
{
    /**
     * Longest path accepted. Depth alone is not what makes an eager load
     * expensive, but an unbounded path is never intentional.
     *
     * @var int
     */
    public const MAX_DEPTH = 5;

    /**
     * @param array $spec the `eager` find parameter
     *
     * @return array
     *
     * @phpstan-param array<array-key, mixed> $spec
     * @phpstan-return mvc_eager_tree
     */
    public static function parse(array $spec): array
    {
        $tree = [];

        foreach ($spec as $key => $value) {
            if (is_string($key)) {
                $path    = $key;
                $options = $value;
            } else {
                $path    = $value;
                $options = [];
            }

            if (!is_string($path)) {
                throw new InvalidEagerPath(
                    is_object($path) ? get_class($path) : gettype($path)
                );
            }

            if (!is_array($options)) {
                throw new InvalidEagerPath($path);
            }

            self::assertOptions($options);

            $segments = explode(".", $path);

            if (count($segments) > self::MAX_DEPTH) {
                throw new InvalidEagerPath($path);
            }

            $tree = self::insert($tree, $path, $segments, 0, $options);
        }

        return $tree;
    }

    /**
     * A per-parent limit requires ROW_NUMBER() OVER (PARTITION BY ...), which
     * PHQL has no syntax for. Applying `limit` to the batch query instead
     * would return N children in total rather than N per parent, which is
     * silently wrong.
     *
     * @param array $options
     *
     * @return void
     *
     * @phpstan-param mvc_model_parameters $options
     */
    private static function assertOptions(array $options): void
    {
        if (isset($options["limit"])) {
            throw new UnsupportedEagerOption("limit");
        }

        if (isset($options["offset"])) {
            throw new UnsupportedEagerOption("offset");
        }
    }

    /**
     * @param array  $tree     accumulated tree
     * @param string $path     the original path, for error messages
     * @param array  $segments exploded path
     * @param int    $index    segment currently being inserted
     * @param array  $options  attach to the last segment only
     *
     * @return array
     *
     * @phpstan-param mvc_eager_tree            $tree
     * @phpstan-param array<array-key, string>  $segments
     * @phpstan-param mvc_model_parameters      $options
     * @phpstan-return mvc_eager_tree
     */
    private static function insert(
        array $tree,
        string $path,
        array $segments,
        int $index,
        array $options
    ): array {
        $segment = trim($segments[$index]);

        if ($segment === "") {
            throw new InvalidEagerPath($path);
        }

        $node = $tree[$segment] ?? [
            "options"  => [],
            "children" => [],
        ];

        if ($index + 1 >= count($segments)) {
            $node["options"] = array_merge($node["options"], $options);
        } else {
            /** @var mvc_eager_tree $children */
            $children = $node["children"];

            $node["children"] = self::insert(
                $children,
                $path,
                $segments,
                $index + 1,
                $options
            );
        }

        $tree[$segment] = $node;

        return $tree;
    }
}

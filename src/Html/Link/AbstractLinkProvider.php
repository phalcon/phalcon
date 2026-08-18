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

namespace Phalcon\Html\Link;

use Phalcon\Contracts\Html\Link\LinkTypes;
use Phalcon\Html\Link\Interfaces\LinkInterface;

use function in_array;
use function is_a;
use function spl_object_hash;

/**
 * @phpstan-import-type link_collection from LinkTypes
 * @phpstan-import-type link_list from LinkTypes
 *
 * @phpstan-property link_collection $links
 */
abstract class AbstractLinkProvider
{
    /**
     * @phpstan-var link_collection
     */
    protected array $links = [];

    /**
     * LinkProvider constructor.
     *
     * The guard keeps foreign objects out of the collection. It stays live in
     * the Zephir implementation, where the array is untyped.
     *
     * @phpstan-param array<array-key, object> $links
     */
    public function __construct(array $links = [])
    {
        foreach ($links as $link) {
            if (
                is_a($link, LinkInterface::class) ||
                is_a($link, "Psr\\Link\\LinkInterface")
            ) {
                /** @phpstan-var LinkInterface $link */
                $this->links[$this->getKey($link)] = $link;
            }
        }
    }

    /**
     * Returns an iterable of LinkInterface objects.
     *
     * The iterable may be an array or any PHP \Traversable object. If no links
     * are available, an empty array or \Traversable MUST be returned.
     *
     * @phpstan-return link_collection
     */
    protected function doGetLinks(): array
    {
        return $this->links;
    }

    /**
     * Returns an iterable of LinkInterface objects that have a specific
     * relationship.
     *
     * The iterable may be an array or any PHP \Traversable object. If no links
     * with that relationship are available, an empty array or \Traversable
     * MUST be returned.
     *
     * @phpstan-return link_list
     */
    protected function doGetLinksByRel(string $rel): array
    {
        $filtered = [];
        foreach ($this->links as $link) {
            $rels = $link->getRels();
            if (true === in_array($rel, $rels)) {
                $filtered[] = $link;
            }
        }

        return $filtered;
    }

    /**
     * Returns an instance with the specified link included.
     *
     * If the specified link is already present, this method MUST return
     * normally without errors. The link is present if $link is === identical
     * to a link object already in the collection.
     *
     * @phpstan-param LinkInterface $link
     */
    protected function doWithLink(mixed $link): static
    {
        $key         = $this->getKey($link);
        $newInstance = clone $this;

        $newInstance->links[$key] = $link;

        return $newInstance;
    }

    /**
     * Returns an instance with the specified link removed.
     *
     * If the specified link is not present, this method MUST return normally
     * without errors. The link is present if $link is === identical to a link
     * object already in the collection.
     *
     * @phpstan-param LinkInterface $link
     */
    protected function doWithoutLink(mixed $link): static
    {
        $key         = $this->getKey($link);
        $newInstance = clone $this;

        unset($newInstance->links[$key]);

        return $newInstance;
    }

    /**
     * Returns the object hash key
     *
     * @phpstan-param object $link
     */
    protected function getKey(mixed $link): string
    {
        return spl_object_hash($link);
    }
}

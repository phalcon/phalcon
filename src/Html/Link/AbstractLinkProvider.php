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

use function in_array;
use function spl_object_hash;

/**
 * @property LinkInterface[] $links
 */
abstract class AbstractLinkProvider
{
    /**
     * @var LinkInterface[]
     */
    protected array $links = [];

    /**
     * LinkProvider constructor.
     *
     * @param array $links
     */
    public function __construct(array $links = [])
    {
        foreach ($links as $link) {
            if ($link instanceof LinkInterface) {
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
     * @return LinkInterface[]
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
     * @return LinkInterface[]
     */
    protected function doGetLinksByRel(string $rel): array
    {
        $filtered = [];
        foreach ($this->links as $link) {
            $rels = $link->getRels();
            if (in_array($rel, $rels)) {
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
     * @param LinkInterface $link
     *   A link object that should be included in this collection.
     *
     * @return $this
     */
    protected function doWithLink(LinkInterface $link)
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
     * @param LinkInterface $link The link to remove.
     *
     * @return $this
     */
    protected function doWithoutLink(LinkInterface $link)
    {
        $key         = $this->getKey($link);
        $newInstance = clone $this;

        unset($newInstance->links[$key]);

        return $newInstance;
    }

    /**
     * Returns the object hash key
     *
     * @param LinkInterface $link
     *
     * @return string
     */
    protected function getKey(LinkInterface $link): string
    {
        return spl_object_hash($link);
    }
}

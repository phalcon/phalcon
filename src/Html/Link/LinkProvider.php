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

use Psr\Link\LinkInterface;
use Psr\Link\LinkProviderInterface;
use Traversable;

use function in_array;
use function spl_object_hash;

/**
 * Class LinkProvider
 *
 * @package Phalcon\Link
 *
 * @property LinkInterface[] $links
 */
class LinkProvider implements LinkProviderInterface
{
    /**
     * @var LinkInterface[]
     */
    protected $links = [];

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
     * @return LinkInterface[]|Traversable
     */
    public function getLinks()
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
     * @return LinkInterface[]|Traversable
     */
    public function getLinksByRel($rel)
    {
        $links = [];
        foreach ($this->links as $link) {
            $rels = $link->getRels();
            if (in_array($rel, $rels)) {
                $links[] = $link;
            }
        }

        return $links;
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

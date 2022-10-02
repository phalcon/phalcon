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

use Phalcon\Support\Collection;

/**
 * @property Collection $attributes
 * @property string     $href
 * @property Collection $rels
 * @property bool       $templated
 */
abstract class AbstractLink
{
    /**
     * @var Collection
     */
    protected Collection $attributes;

    /**
     * @var string
     */
    protected string $href = "";

    /**
     * @var Collection
     */
    protected Collection $rels;

    /**
     * @var bool
     */
    protected bool $templated = false;

    /**
     * Link constructor.
     *
     * @param string $rel
     * @param string $href
     * @param array  $attributes
     */
    public function __construct(
        string $rel = "",
        string $href = "",
        array $attributes = []
    ) {
        $this->attributes = new Collection($attributes);
        $this->rels       = new Collection();
        $this->href       = $href;
        $this->templated  = $this->hrefIsTemplated($href);

        if (true !== empty($rel)) {
            $this->rels->set($rel, true);
        }
    }

    /**
     * Returns a list of attributes that describe the target URI.
     *
     * @return array
     *   A key-value list of attributes, where the key is a string and the value
     *  is either a PHP primitive or an array of PHP strings. If no values are
     *  found an empty array MUST be returned.
     */
    protected function doGetAttributes(): array
    {
        return $this->attributes->toArray();
    }

    /**
     * Returns the target of the link.
     *
     * The target link must be one of:
     * - An absolute URI, as defined by RFC 5988.
     * - A relative URI, as defined by RFC 5988. The base of the relative link
     *     is assumed to be known based on context by the client.
     * - A URI template as defined by RFC 6570.
     *
     * If a URI template is returned, isTemplated() MUST return True.
     *
     * @return string
     */
    protected function doGetHref(): string
    {
        return $this->href;
    }

    /**
     * Returns the relationship type(s) of the link.
     *
     * This method returns 0 or more relationship types for a link, expressed
     * as an array of strings.
     *
     * @return string[]
     */
    protected function doGetRels(): array
    {
        return $this->rels->getKeys(false);
    }

    /**
     * Returns whether this is a templated link.
     *
     * @return bool
     *   True if this link object is templated, False otherwise.
     */
    protected function doIsTemplated(): bool
    {
        return $this->templated;
    }

    /**
     * Determines if a href is a templated link or not.
     *
     * @see https://tools.ietf.org/html/rfc6570
     *
     * @param string $href
     *
     * @return bool
     */
    protected function hrefIsTemplated(string $href): bool
    {
        return (
            false !== mb_strpos($href, "{") &&
            false !== mb_strpos($href, "}")
        );
    }

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return $this
     */
    protected function doWithAttribute(string $key, mixed $value): self
    {
        $newInstance = clone $this;

        $newInstance->attributes->set($key, $value);

        return $newInstance;
    }

    /**
     * @param string $href
     *
     * @return $this
     */
    protected function doWithHref(string $href): self
    {
        $newInstance = clone $this;

        $newInstance->href      = $href;
        $newInstance->templated = $this->hrefIsTemplated($href);

        return $newInstance;
    }

    /**
     * @param string $key
     *
     * @return $this
     */
    protected function doWithRel(string $key): self
    {
        $newInstance = clone $this;

        $newInstance->rels->set($key, true);

        return $newInstance;
    }

    /**
     * @param string $key
     *
     * @return $this
     */
    protected function doWithoutAttribute(string $key): self
    {
        $newInstance = clone $this;

        $newInstance->attributes->remove($key);

        return $newInstance;
    }

    /**
     * @param string $key
     *
     * @return $this
     */
    protected function doWithoutRel(string $key): self
    {
        $newInstance = clone $this;

        $newInstance->rels->remove($key);

        return $newInstance;
    }
}

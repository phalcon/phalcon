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

use function array_keys;
use function strpos;
use function strrpos;

/**
 * Class Link
 *
 * @package Phalcon\Link
 *
 * @property array  $attributes
 * @property string $href
 * @property array  $rels
 * @property bool   $templated
 */
class Link implements LinkInterface
{
    /**
     * @var array
     */
    protected array $attributes = [];

    /**
     * @var string
     */
    protected string $href = '';

    /**
     * @var array
     */
    protected array $rels = [];

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
    public function __construct(string $rel = '', string $href = '', array $attributes = [])
    {
        if (true !== empty($rel)) {
            $this->rels[$rel] = true;
        }

        $this->attributes = $attributes;
        $this->href       = $href;
        $this->templated  = $this->hrefIsTemplated($href);
    }

    /**
     * Returns a list of attributes that describe the target URI.
     *
     * @return array
     *   A key-value list of attributes, where the key is a string and the value
     *  is either a PHP primitive or an array of PHP strings. If no values are
     *  found an empty array MUST be returned.
     */
    public function getAttributes()
    {
        return $this->attributes;
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
    public function getHref()
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
    public function getRels()
    {
        return array_keys($this->rels);
    }

    /**
     * Returns whether or not this is a templated link.
     *
     * @return bool
     *   True if this link object is templated, False otherwise.
     */
    public function isTemplated()
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
            false !== strpos($href, '{') &&
            false !== strrpos($href, '}')
        );
    }
}

<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Html\Helper;

use Phalcon\Html\Exception;

/**
 * Class Element
 */
class Element extends AbstractHelper
{
    /**
     * Produce a tag.
     *
     * @param string $tag
     * @param string $text
     * @param array  $attributes
     * @param bool   $raw
     *
     * @return string
     * @throws Exception
     */
    public function __invoke(
        string $tag,
        string $text,
        array $attributes = [],
        bool $raw = false
    ): string {
        return $this->renderFullElement($tag, $text, $attributes, $raw);
    }
}

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
 * Generic open-tag escape hatch. Renders just `<name attr="...">` for any
 * tag name without a dedicated helper. For an open + content + close tag
 * use `Element` instead. For self-closing void tags (img, br, hr, etc.)
 * use `VoidTag`.
 */
class Tag extends AbstractHelper
{
    /**
     * @param string $name
     * @param array  $attributes
     *
     * @return string
     * @throws Exception
     */
    public function __invoke(string $name, array $attributes = []): string
    {
        return $this->renderTag($name, $attributes);
    }
}

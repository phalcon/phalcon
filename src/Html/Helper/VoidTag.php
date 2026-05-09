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
 * Generic void-tag escape hatch. Renders a self-closing tag for any name
 * without a dedicated helper. The trailing `/` is emitted only for XHTML
 * doctypes, matching the `Input/AbstractInput::__toString` convention.
 */
class VoidTag extends AbstractHelper
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
        $closeTag = '';
        if (null !== $this->doctype && $this->doctype->getType() > Doctype::HTML5) {
            $closeTag = '/';
        }

        return $this->renderTag($name, $attributes, $closeTag);
    }
}

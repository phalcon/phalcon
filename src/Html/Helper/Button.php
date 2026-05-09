<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Html\Helper;

use Phalcon\Html\Escaper\EscaperInterface;

/**
 * Button class producing "button" elements
 */
class Button extends AbstractHelper
{
    /**
     * @param EscaperInterface $escaper
     * @param Doctype|null     $doctype
     * @param bool             $forceRaw
     */
    public function __construct(
        EscaperInterface $escaper,
        ?Doctype $doctype = null,
        protected bool $forceRaw = false
    ) {
        parent::__construct($escaper, $doctype);
    }

    /**
     * Produce a `<button>` tag.
     *
     * @param string $text
     * @param array  $attributes
     * @param bool   $raw
     *
     * @return string
     */
    public function __invoke(
        string $text,
        array $attributes = [],
        bool $raw = false
    ): string {
        return $this->renderFullElement('button', $text, $attributes, $raw || $this->forceRaw);
    }
}

<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Implementation of this file has been influenced by AuraPHP
 * @link    https://github.com/auraphp/Aura.Html
 * @license https://github.com/auraphp/Aura.Html/blob/2.x/LICENSE
 */

declare(strict_types=1);

namespace Phalcon\Html\Helper;

use Phalcon\Contracts\Html\HtmlTypes;
use Phalcon\Html\Escaper\EscaperInterface;

/**
 * Class Button
 *
 * @phpstan-import-type html_attributes from HtmlTypes
 *
 * @property bool $forceRaw
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
     * @phpstan-param html_attributes $attributes
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

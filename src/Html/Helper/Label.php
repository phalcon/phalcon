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
 * Class Label
 *
 * @property bool $forceRaw
 */
class Label extends AbstractHelper
{
    /**
     * @var bool
     */
    protected bool $forceRaw = false;

    /**
     * @param EscaperInterface $escaper
     * @param Doctype|null     $doctype
     * @param bool             $forceRaw
     */
    public function __construct(
        EscaperInterface $escaper,
        ?Doctype $doctype = null,
        bool $forceRaw = false
    ) {
        parent::__construct($escaper, $doctype);

        $this->forceRaw = $forceRaw;
    }

    /**
     * Produce a `<label>` tag.
     *
     * @param string $label
     * @param array  $attributes
     * @param bool   $raw
     *
     * @return string
     */
    public function __invoke(
        string $label,
        array $attributes = [],
        bool $raw = false
    ): string {
        return $this->renderFullElement('label', $label, $attributes, $raw || $this->forceRaw);
    }
}

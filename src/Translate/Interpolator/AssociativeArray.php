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

namespace Phalcon\Translate\Interpolator;

use Phalcon\Traits\Helper\Str\InterpolateTrait;

/**
 * Class AssociativeArray
 *
 * @package Phalcon\Translate\Interpolator
 */
class AssociativeArray implements InterpolatorInterface
{
    use InterpolateTrait;

    /**
     * Replaces placeholders by the values passed
     *
     * @param string $translation
     * @param array  $placeholders
     *
     * @return string
     */
    public function replacePlaceholders(
        string $translation,
        array $placeholders = []
    ): string {
        return $this->toInterpolate($translation, $placeholders);
    }
}

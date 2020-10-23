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

use function str_replace;

/**
 * Class AssociativeArray
 *
 * @package Phalcon\Translate\Interpolator
 */
class AssociativeArray implements InterpolatorInterface
{
    /**
     * Replaces placeholders by the values passed
     */
    public function replacePlaceholders(
        string $translation,
        array $placeholders = []
    ): string {
        foreach ($placeholders as $key => $placeholder) {
            $translation = str_replace(
                '%' . $key . '%',
                $placeholder,
                $translation
            );
        }

        return $translation;
    }
}

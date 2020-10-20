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

namespace Phalcon\Tests\Fixtures\Traits;

trait VersionTrait
{
    /**
     * Translates a number to a special version string (alpha, beta, RC)
     */
    protected function numberToSpecial(string $number): string
    {
        $map = [
            1 => 'alpha',
            2 => 'beta',
            3 => 'RC',
        ];

        return $map[$number] ?? '';
    }

    /**
     * Translates a special version (alpha, beta, RC) to a version number
     */
    protected function specialToNumber(string $input): string
    {
        $map = [
            'alpha' => '1',
            'beta'  => '2',
            'RC'    => '3',
        ];

        return $map[$input] ?? '4';
    }
}

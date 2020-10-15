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

namespace Phalcon\Storage\Traits;

trait StorageErrorHandlerTrait
{
    /**
     * @param string $method
     * @param int    $errorType
     * @param mixed  $data
     * @param mixed  $defaultValue
     *
     * @return mixed
     */
    private function callMethodWithError(
        string $method,
        int $errorType,
        $data,
        $defaultValue = null
    ) {
        $warning = false;
        set_error_handler(
            function () use (&$warning) {
                $warning = true;
            },
            $errorType
        );

        $data = $method($data);

        restore_error_handler();

        if (true === $warning) {
            $data = $defaultValue;
        }

        return $data;
    }
}

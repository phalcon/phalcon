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

namespace Phalcon\Tests\Fixtures\Filter\Validation\Validator\File;

use Phalcon\Filter\Validation\Validator\File\MimeType;

class MimeTypeFixture extends MimeType
{
    /**
     * Return true if the given function has been defined
     *
     * @param string $function
     *
     * @return bool
     *
     * @link https://php.net/manual/en/function.function-exists.php
     */
    protected function phpFunctionExists(string $function)
    {
        return false;
    }

    /**
     * Checks if a file has been uploaded; Internal check that can be
     * overridden in a subclass if you do not want to check uploaded files
     *
     * @param string $name
     *
     * @return bool
     */
    protected function checkIsUploadedFile(string $name): bool
    {
        return true;
    }
}

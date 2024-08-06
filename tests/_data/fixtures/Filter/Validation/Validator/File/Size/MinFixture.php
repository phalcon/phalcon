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

namespace Phalcon\Tests\Fixtures\Filter\Validation\Validator\File\Size;

use Phalcon\Filter\Validation\Validator\File\Size\Min;

class MinFixture extends Min
{
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

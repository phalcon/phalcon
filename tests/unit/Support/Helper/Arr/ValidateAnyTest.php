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

namespace Phalcon\Tests\Unit\Support\Helper\Arr;

use Phalcon\Support\Helper\Arr\ValidateAny;
use Phalcon\Tests\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\Test;

final class ValidateAnyTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Support\Helper\Arr :: validateAny()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    #[Test]
    public function testSupportHelperArrValidateAny(): void
    {
        $object = new ValidateAny();
        $collection = [1, 2, 3, 4, 5];
        $actual = $object(
            $collection,
            function ($element) {
                return $element < 2;
            }
        );
        $this->assertTrue($actual);
    }
}

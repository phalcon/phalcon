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

namespace Phalcon\Tests\Unit\Tag;

use Phalcon\Tag;
use Phalcon\Tests\Fixtures\Helpers\TagSetup;

class SetDocTypeTest extends TagSetup
{
    /**
     * Tests Phalcon\Tag :: setDocType()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function testTagSetDocType(): void
    {
        $this->runDoctypeTest(Tag::HTML32);
    }
}

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

use Phalcon\Html\Escaper;
use Phalcon\Html\Escaper\EscaperInterface;
use Phalcon\Tag;
use Phalcon\Tests\Fixtures\Helpers\AbstractTagSetup;

class GetEscaperTest extends AbstractTagSetup
{
    /**
     * Tests Phalcon\Tag :: getEscaper()
     *
     * @since  2018-11-13
     *
     * @author Phalcon Team <team@phalcon.io>
     */
    public function testTagGetEscaper(): void
    {
        $escaper = Tag::getEscaper([]);
        $this->assertInstanceOf(Escaper::class, $escaper);
        $this->assertInstanceOf(EscaperInterface::class, $escaper);

        $escaper = Tag::getEscaper(["escape" => false]);
        $this->assertNull($escaper);
    }
}

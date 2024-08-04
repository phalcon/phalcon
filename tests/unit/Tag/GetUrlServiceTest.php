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

use Phalcon\Mvc\Url;
use Phalcon\Mvc\Url\UrlInterface;
use Phalcon\Tag;
use Phalcon\Tests\Fixtures\Helpers\TagSetup;

class GetUrlServiceTest extends TagSetup
{
    /**
     * Tests Phalcon\Tag :: getUrlService()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function testTagGetUrlService(): void
    {
        $url = Tag::getUrlService();

        $this->assertInstanceOf(Url::class, $url);
        $this->assertInstanceOf(UrlInterface::class, $url);
    }
}

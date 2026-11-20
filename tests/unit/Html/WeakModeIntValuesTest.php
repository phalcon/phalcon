<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Unit\Html;

use Phalcon\Html\Escaper;
use Phalcon\Html\Helper\Input\Select\ResultsetData;
use Phalcon\Html\Link\EvolvableLink;
use Phalcon\Html\Link\Link;
use Phalcon\Html\Link\LinkProvider;
use Phalcon\Html\TagFactory;
use Phalcon\Talon\PHPUnit\AbstractUnitTestCase;
use Phalcon\Tests\Support\Fake\FakeRow;
use Phalcon\Tests\Unit\Html\Helper\Input\Select\Fake\FakeResultset;

/**
 * cphalcon uses the PHP weak type rules for an int value that goes to a string
 * target, thus the int becomes a string.
 *
 * @issue https://github.com/phalcon/phalcon/issues/887
 */
final class WeakModeIntValuesTest extends AbstractUnitTestCase
{
    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-20
     */
    public function testHtmlHelperInputSelectResultsetDataUsingInt(): void
    {
        $data = new ResultsetData(
            new FakeResultset([new FakeRow(['id' => 1, 'name' => 'one'])]),
            [0, 1]
        );

        $this->assertSame(['' => null], $data->getOptions());
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-20
     */
    public function testHtmlHelperInputTextareaValueInt(): void
    {
        $factory = new TagFactory(new Escaper());

        $this->assertSame(
            '<textarea id="bio" name="bio">123</textarea>',
            (string) $factory->inputTextarea('bio', null, ['value' => 123])
        );
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-20
     */
    public function testHtmlLinkEvolvableLinkWithAttributeNameInt(): void
    {
        $link = (new EvolvableLink('rel', 'href'))->withAttribute(1, 'x');

        $this->assertSame([1 => 'x'], $link->getAttributes());
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-20
     */
    public function testHtmlLinkLinkProviderGetLinksByRelInt(): void
    {
        $provider = new LinkProvider([new Link('1', 'href')]);

        $this->assertCount(1, $provider->getLinksByRel(1));
    }
}

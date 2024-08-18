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

namespace Phalcon\Tests\Unit\Translate\Adapter\Csv;

use ArrayAccess;
use Phalcon\Tests\Fixtures\Traits\TranslateCsvTrait;
use Phalcon\Tests\Fixtures\Translate\Adapter\CsvFopenFixture;
use Phalcon\Tests\AbstractUnitTestCase;
use Phalcon\Translate\Adapter\AdapterInterface;
use Phalcon\Translate\Adapter\Csv;
use Phalcon\Translate\Exception;
use Phalcon\Translate\InterpolatorFactory;
use PHPUnit\Framework\Attributes\Test;

use function dataDir;

final class ConstructTest extends AbstractUnitTestCase
{
    use TranslateCsvTrait;

    /**
     * Tests Phalcon\Translate\Adapter\Csv :: __construct()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    #[Test]
    public function testTranslateAdapterCsvConstruct(): void
    {
        $language   = $this->getCsvConfig()['en'];
        $translator = new Csv(new InterpolatorFactory(), $language);

        $this->assertInstanceOf(ArrayAccess::class, $translator);
        $this->assertInstanceOf(AdapterInterface::class, $translator);
    }

    /**
     * Tests Phalcon\Translate\Adapter\Csv :: __construct() - Exception
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    #[Test]
    public function testTranslateAdapterCsvContentParamExist(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Parameter 'content' is required");

        (new Csv(new InterpolatorFactory(), []));
    }

    /**
     * Tests Phalcon\Translate\Adapter\Csv :: __construct() - Exception error
     * loading file
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    #[Test]
    public function testTranslateAdapterCsvErrorLoadingFile(): void
    {
        $message = "Error opening translation file '"
            . dataDir('assets/translation/csv/en.csv') . "'";
        $this->expectException(Exception::class);
        $this->expectExceptionMessage($message);

        $language = $this->getCsvConfig()['en'];
        (new CsvFopenFixture(new InterpolatorFactory(), $language));
    }
}

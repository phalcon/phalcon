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

namespace Phalcon\Tests\Unit\Annotations\Adapter;

use AnnotationsTestClass;
use Phalcon\Annotations\Adapter\Stream;
use Phalcon\Annotations\Exception;
use Phalcon\Annotations\Reflection;
use Phalcon\Tests\Fixtures\Annotations\Adapter\StreamFileExistsFixture;
use Phalcon\Tests\Fixtures\Annotations\Adapter\StreamFileGetContentsFixture;
use Phalcon\Tests\Fixtures\Annotations\Adapter\StreamFilePutContentsFixture;
use Phalcon\Tests\Fixtures\Annotations\Adapter\StreamUnserializeFixture;
use Phalcon\Tests\Fixtures\Traits\AnnotationsTrait;
use Phalcon\Tests\UnitTestCase;
use RuntimeException;

use function outputDir;
use function safeDeleteFile;
use function supportDir;

final class ReadWriteTest extends UnitTestCase
{
    use AnnotationsTrait;

    /**
     * Tests Phalcon\Annotations\Adapter :: read()
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2022-12-30
     */
    public function testAnnotationsAdapterReadFileExists(): void
    {
        $parameters = [
            'annotationsDir' => outputDir('tests/annotations/'),
        ];
        $adapter    = new StreamFileExistsFixture($parameters);

        $actual = $adapter->read('testwrite');
        $this->assertFalse($actual);
    }

    /**
     * Tests Phalcon\Annotations\Adapter :: read()
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2022-12-30
     */
    public function testAnnotationsAdapterReadFileGetContents(): void
    {
        require_once dataDir('fixtures/Annotations/AnnotationsTestClass.php');
        $parameters = [
            'annotationsDir' => outputDir('tests/annotations/'),
        ];
        $adapter    = new StreamFileGetContentsFixture($parameters);
        $adapter->get(AnnotationsTestClass::class);

        $actual = $adapter->read('testprop1');
        $this->assertFalse($actual);
    }

    /**
     * Tests Phalcon\Annotations\Adapter :: read()
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2022-12-30
     */
    public function testAnnotationsAdapterReadUnserialize(): void
    {
        require_once dataDir('fixtures/Annotations/AnnotationsTestClass.php');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot read annotation data');

        $parameters = [
            'annotationsDir' => outputDir('tests/annotations/'),
        ];
        $adapter    = new StreamUnserializeFixture($parameters);
        $adapter->get(AnnotationsTestClass::class);
        $adapter->read('testprop1');
    }

    /**
     * Tests Phalcon\Annotations\Adapter :: read()/write()
     *
     * @dataProvider getExamples
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2022-12-30
     */
    public function testAnnotationsAdapterReadWrite(
        string $class,
        array $params
    ): void {
        require_once dataDir('fixtures/Annotations/AnnotationsTestClass.php');

        $adapter          = new $class($params);
        $classAnnotations = $adapter->get(AnnotationsTestClass::class);

        $adapter->write('testwrite', $classAnnotations);

        if (Stream::class === $class) {
            $this->assertFileExists(
                outputDir('tests/annotations/testwrite.php')
            );
        }

        $newClass = $adapter->read('testwrite');
        $expected = Reflection::class;
        $actual   = $newClass;
        $this->assertInstanceOf($expected, $actual);

        if (Stream::class === $class) {
            $this->safeDeleteFile(outputDir('tests/annotations/testwrite.php'));
        }
    }

    /**
     * Tests Phalcon\Annotations\Adapter :: read()
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2022-12-30
     */
    public function testAnnotationsAdapterWriteFilePutContents(): void
    {
        require_once dataDir('fixtures/Annotations/AnnotationsTestClass.php');
        $parameters = [
            'annotationsDir' => outputDir('tests/annotations/'),
        ];
        $adapter    = new StreamFilePutContentsFixture($parameters);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Annotations directory cannot be written');

        $classAnnotations = $adapter->get(AnnotationsTestClass::class);
        $adapter->write('testwrite', $classAnnotations);
    }
}

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

namespace Phalcon\Tests\Unit\Annotations\Reader;

use Phalcon\Annotations\Exception;
use Phalcon\Annotations\Reader;
use Phalcon\Tests\AbstractUnitTestCase;
use ReflectionException;

use function supportDir;

final class ParseTest extends AbstractUnitTestCase
{
    /**
     * Test throws Phalcon\Annotations\Exception when got final class with invalid
     * annotation
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2016-01-25
     */
    public function testParseWithInvalidAnnotation(): void
    {
        $includeFile = dataDir('fixtures/Annotations/AnnotationsTestInvalid.php');
        $this->assertFileExists($includeFile);

        require_once $includeFile;

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Syntax error, unexpected EOF in ' . $includeFile);

        $reader = new Reader();
        $reader->parse('AnnotationsTestInvalid');
    }

    /**
     * Test throws ReflectionException when non-existent got class
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2016-01-25
     */
    public function testParseWithNonExistentClass(): void
    {
        $message = 'Class "TestClass1" does not exist';
        $this->expectException(ReflectionException::class);
        $this->expectExceptionMessage($message);

        $reader = new Reader();
        $reader->parse('TestClass1');
    }

    /**
     * Tests Reader::parse
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2016-01-25
     */
    public function testReaderParse(): void
    {
        $includeFile = dataDir('fixtures/Annotations/AnnotationsTestClass.php');

        $this->assertFileExists($includeFile);

        require_once $includeFile;

        $reader  = new Reader();
        $parsing = $reader->parse('AnnotationsTestClass');

        $actual = isset($parsing['class']);
        $this->assertTrue($actual);

        $expected = 9;
        $actual   = $parsing['class'];
        $this->assertCount($expected, $actual);

        // Simple
        $expected = 'Simple';
        $actual   = $parsing['class'][0]['name'];
        $this->assertSame($expected, $actual);

        $actual = isset($parsing['class'][0]['arguments']);
        $this->assertFalse($actual);

        // Single Param
        $expected = 'SingleParam';
        $actual   = $parsing['class'][1]['name'];
        $this->assertSame($expected, $actual);

        $actual = isset($parsing['class'][1]['arguments']);
        $this->assertTrue($actual);

        $expected = 1;
        $actual   = $parsing['class'][1]['arguments'];
        $this->assertCount($expected, $actual);

        $expected = 'Param';
        $actual   = $parsing['class'][1]['arguments'][0]['expr']['value'];
        $this->assertSame($expected, $actual);

        // Multiple Params
        $expected = 8;
        $actual   = $parsing['class'][2]['arguments'];
        $this->assertCount($expected, $actual);

        $actual = isset($parsing['class'][2]['arguments']);
        $this->assertTrue($actual);

        $expected = 'MultipleParams';
        $actual   = $parsing['class'][2]['name'];
        $this->assertSame($expected, $actual);

        $expected = 'First';
        $actual   = $parsing['class'][2]['arguments'][0]['expr']['value'];
        $this->assertSame($expected, $actual);

        $expected = 'Second';
        $actual   = $parsing['class'][2]['arguments'][1]['expr']['value'];
        $this->assertSame($expected, $actual);

        $expected = '1';
        $actual   = $parsing['class'][2]['arguments'][2]['expr']['value'];
        $this->assertSame($expected, $actual);

        $expected = '1.1';
        $actual   = $parsing['class'][2]['arguments'][3]['expr']['value'];
        $this->assertSame($expected, $actual);

        $expected = '-10';
        $actual   = $parsing['class'][2]['arguments'][4]['expr']['value'];
        $this->assertSame($expected, $actual);

        $expected = 305;
        $actual   = $parsing['class'][2]['arguments'][5]['expr']['type'];
        $this->assertSame($expected, $actual);

        $expected = 306;
        $actual   = $parsing['class'][2]['arguments'][6]['expr']['type'];
        $this->assertSame($expected, $actual);

        $expected = 304;
        $actual   = $parsing['class'][2]['arguments'][7]['expr']['type'];
        $this->assertSame($expected, $actual);


        // Single Array Param
        $expected = 1;
        $actual   = $parsing['class'][3]['arguments'];
        $this->assertCount($expected, $actual);

        $expected = 3;
        $actual   = $parsing['class'][3]['arguments'][0]['expr']['items'];
        $this->assertCount($expected, $actual);

        $actual = isset($parsing['class'][3]['arguments']);
        $this->assertTrue($actual);

        $expected = 'Params';
        $actual   = $parsing['class'][3]['name'];
        $this->assertSame($expected, $actual);

        $expected = 308;
        $actual   = $parsing['class'][3]['arguments'][0]['expr']['type'];
        $this->assertSame($expected, $actual);

        $expected = 'key1';
        $actual   = $parsing['class'][3]['arguments'][0]['expr']['items'][0]['expr']['value'];
        $this->assertSame($expected, $actual);

        $expected = 'key2';
        $actual   = $parsing['class'][3]['arguments'][0]['expr']['items'][1]['expr']['value'];
        $this->assertSame($expected, $actual);

        $expected = 'key3';
        $actual   = $parsing['class'][3]['arguments'][0]['expr']['items'][2]['expr']['value'];
        $this->assertSame($expected, $actual);


        // Hash Params
        $expected = 1;
        $actual   = $parsing['class'][8]['arguments'];
        $this->assertCount($expected, $actual);

        $expected = 3;
        $actual   = $parsing['class'][4]['arguments'][0]['expr']['items'];
        $this->assertCount($expected, $actual);

        $actual = isset($parsing['class'][4]['arguments']);
        $this->assertTrue($actual);

        $expected = 'HashParams';
        $actual   = $parsing['class'][4]['name'];
        $this->assertSame($expected, $actual);

        $expected = 308;
        $actual   = $parsing['class'][4]['arguments'][0]['expr']['type'];
        $this->assertSame($expected, $actual);

        $expected = 'key1';
        $actual   = $parsing['class'][4]['arguments'][0]['expr']['items'][0]['name'];
        $this->assertSame($expected, $actual);

        $expected = 'value';
        $actual   = $parsing['class'][4]['arguments'][0]['expr']['items'][0]['expr']['value'];
        $this->assertSame($expected, $actual);

        $expected = 'key2';
        $actual   = $parsing['class'][4]['arguments'][0]['expr']['items'][1]['name'];
        $this->assertSame($expected, $actual);

        $expected = 'value';
        $actual   = $parsing['class'][4]['arguments'][0]['expr']['items'][1]['expr']['value'];
        $this->assertSame($expected, $actual);

        $expected = 'key3';
        $actual   = $parsing['class'][4]['arguments'][0]['expr']['items'][2]['name'];
        $this->assertSame($expected, $actual);

        $expected = 'value';
        $actual   = $parsing['class'][4]['arguments'][0]['expr']['items'][2]['expr']['value'];
        $this->assertSame($expected, $actual);


        // Named Params
        $expected = 2;
        $actual   = $parsing['class'][5]['arguments'];
        $this->assertCount($expected, $actual);

        $actual = isset($parsing['class'][5]['arguments']);
        $this->assertTrue($actual);

        $expected = 'NamedParams';
        $actual   = $parsing['class'][5]['name'];
        $this->assertSame($expected, $actual);

        $expected = 'second';
        $actual   = $parsing['class'][5]['arguments'][1]['name'];
        $this->assertSame($expected, $actual);

        $expected = 'other';
        $actual   = $parsing['class'][5]['arguments'][1]['expr']['value'];
        $this->assertSame($expected, $actual);

        $expected = 'second';
        $actual   = $parsing['class'][5]['arguments'][1]['name'];
        $this->assertSame($expected, $actual);

        $expected = 'other';
        $actual   = $parsing['class'][5]['arguments'][1]['expr']['value'];
        $this->assertSame($expected, $actual);


        // Alternative Named Params
        $expected = 2;
        $actual   = $parsing['class'][6]['arguments'];
        $this->assertCount($expected, $actual);

        $actual = isset($parsing['class'][6]['arguments']);
        $this->assertTrue($actual);

        $expected = 'AlternativeNamedParams';
        $actual   = $parsing['class'][6]['name'];
        $this->assertSame($expected, $actual);

        $expected = 'second';
        $actual   = $parsing['class'][6]['arguments'][1]['name'];
        $this->assertSame($expected, $actual);

        $expected = 'other';
        $actual   = $parsing['class'][6]['arguments'][1]['expr']['value'];
        $this->assertSame($expected, $actual);

        $expected = 'second';
        $actual   = $parsing['class'][6]['arguments'][1]['name'];
        $this->assertSame($expected, $actual);

        $expected = 'other';
        $actual   = $parsing['class'][6]['arguments'][1]['expr']['value'];
        $this->assertSame($expected, $actual);


        // Alternative Hash Params
        $expected = 1;
        $actual   = $parsing['class'][7]['arguments'];
        $this->assertCount($expected, $actual);

        $expected = 3;
        $actual   = $parsing['class'][7]['arguments'][0]['expr']['items'];
        $this->assertCount($expected, $actual);

        $actual = isset($parsing['class'][7]['arguments']);
        $this->assertTrue($actual);

        $expected = 'AlternativeHashParams';
        $actual   = $parsing['class'][7]['name'];
        $this->assertSame($expected, $actual);

        $expected = 308;
        $actual   = $parsing['class'][7]['arguments'][0]['expr']['type'];
        $this->assertSame($expected, $actual);

        $expected = 'key1';
        $actual   = $parsing['class'][7]['arguments'][0]['expr']['items'][0]['name'];
        $this->assertSame($expected, $actual);

        $expected = 'value';
        $actual   = $parsing['class'][7]['arguments'][0]['expr']['items'][0]['expr']['value'];
        $this->assertSame($expected, $actual);

        $expected = 'key2';
        $actual   = $parsing['class'][7]['arguments'][0]['expr']['items'][1]['name'];
        $this->assertSame($expected, $actual);

        $expected = 'value';
        $actual   = $parsing['class'][7]['arguments'][0]['expr']['items'][1]['expr']['value'];
        $this->assertSame($expected, $actual);

        $expected = 'key3';
        $actual   = $parsing['class'][7]['arguments'][0]['expr']['items'][2]['name'];
        $this->assertSame($expected, $actual);

        $expected = 'value';
        $actual   = $parsing['class'][7]['arguments'][0]['expr']['items'][2]['expr']['value'];
        $this->assertSame($expected, $actual);

        // Recursive Hash
        $expected = 1;
        $actual   = $parsing['class'][8]['arguments'];
        $this->assertCount($expected, $actual);

        $expected = 3;
        $actual   = $parsing['class'][8]['arguments'][0]['expr']['items'];
        $this->assertCount($expected, $actual);

        $actual = isset($parsing['class'][8]['arguments']);
        $this->assertTrue($actual);

        $expected = 'RecursiveHash';
        $actual   = $parsing['class'][8]['name'];
        $this->assertSame($expected, $actual);

        $expected = 308;
        $actual   = $parsing['class'][8]['arguments'][0]['expr']['type'];
        $this->assertSame($expected, $actual);

        $expected = 'key1';
        $actual   = $parsing['class'][8]['arguments'][0]['expr']['items'][0]['name'];
        $this->assertSame($expected, $actual);

        $expected = 'value';
        $actual   = $parsing['class'][8]['arguments'][0]['expr']['items'][0]['expr']['value'];
        $this->assertSame($expected, $actual);

        $expected = 'key2';
        $actual   = $parsing['class'][8]['arguments'][0]['expr']['items'][1]['name'];
        $this->assertSame($expected, $actual);

        $expected = 'value';
        $actual   = $parsing['class'][8]['arguments'][0]['expr']['items'][1]['expr']['value'];
        $this->assertSame($expected, $actual);

        $expected = 'key3';
        $actual   = $parsing['class'][8]['arguments'][0]['expr']['items'][2]['name'];
        $this->assertSame($expected, $actual);

        $expected = 308;
        $actual   = $parsing['class'][8]['arguments'][0]['expr']['items'][2]['expr']['type'];
        $this->assertSame($expected, $actual);


        // Constants
        $expected = 1;
        $actual   = $parsing['constants'];
        $this->assertCount($expected, $actual);

        $actual = isset($parsing['constants']);
        $this->assertTrue($actual);

        $actual = isset($parsing['constants']['TEST_CONST2']);
        $this->assertFalse($actual);

        $expected = 'Simple';
        $actual   = $parsing['constants']['TEST_CONST1'][0]['name'];
        $this->assertSame($expected, $actual);

        // Properties
        $expected = 3;
        $actual   = $parsing['properties'];
        $this->assertCount($expected, $actual);

        $actual = isset($parsing['properties']);
        $this->assertTrue($actual);

        // Multiple well-ordered annotations
        $expected = 4;
        $actual   = $parsing['properties']['testProp1'];
        $this->assertCount($expected, $actual);

        $actual = isset($parsing['properties']['testProp1']);
        $this->assertTrue($actual);

        $expected = 'var';
        $actual   = $parsing['properties']['testProp1'][0]['name'];
        $this->assertSame($expected, $actual);

        $expected = 'Simple';
        $actual   = $parsing['properties']['testProp1'][1]['name'];
        $this->assertSame($expected, $actual);

        $expected = 'SingleParam';
        $actual   = $parsing['properties']['testProp1'][2]['name'];
        $this->assertSame($expected, $actual);

        $expected = 'MultipleParams';
        $actual   = $parsing['properties']['testProp1'][3]['name'];
        $this->assertSame($expected, $actual);

        // Comment without content
        $actual = isset($parsing['properties']['testProp2']);
        $this->assertFalse($actual);

        // Same line annotations
        $expected = 3;
        $actual   = $parsing['properties']['testProp3'];
        $this->assertCount($expected, $actual);

        $expected = 'Simple';
        $actual   = $parsing['properties']['testProp3'][0]['name'];
        $this->assertSame($expected, $actual);

        $expected = 'SingleParam';
        $actual   = $parsing['properties']['testProp3'][1]['name'];
        $this->assertSame($expected, $actual);

        $expected = 'MultipleParams';
        $actual   = $parsing['properties']['testProp3'][2]['name'];
        $this->assertSame($expected, $actual);

        // Same line annotations
        $expected = 3;
        $actual   = $parsing['properties']['testProp4'];
        $this->assertCount($expected, $actual);

        $expected = 'Simple';
        $actual   = $parsing['properties']['testProp4'][0]['name'];
        $this->assertSame($expected, $actual);

        $expected = 'SingleParam';
        $actual   = $parsing['properties']['testProp4'][1]['name'];
        $this->assertSame($expected, $actual);

        $expected = 'MultipleParams';
        $actual   = $parsing['properties']['testProp4'][2]['name'];
        $this->assertSame($expected, $actual);


        // No docblock
        $actual = isset($parsing['properties']['testMethod5']);
        $this->assertFalse($actual);

        // No annotations
        $actual = isset($parsing['properties']['testMethod6']);
        $this->assertFalse($actual);

        // Properties
        $expected = 4;
        $actual   = $parsing['methods'];
        $this->assertCount($expected, $actual);

        $actual = isset($parsing['methods']);
        $this->assertTrue($actual);

        // Multiple well-ordered annotations
        $expected = 5;
        $actual   = $parsing['methods']['testMethod1'];
        $this->assertCount($expected, $actual);

        $actual = isset($parsing['methods']['testMethod1']);
        $this->assertTrue($actual);

        $expected = 'return';
        $actual   = $parsing['methods']['testMethod1'][0]['name'];
        $this->assertSame($expected, $actual);

        $expected = 'Simple';
        $actual   = $parsing['methods']['testMethod1'][1]['name'];
        $this->assertSame($expected, $actual);

        $expected = 'SingleParam';
        $actual   = $parsing['methods']['testMethod1'][2]['name'];
        $this->assertSame($expected, $actual);

        $expected = 'MultipleParams';
        $actual   = $parsing['methods']['testMethod1'][3]['name'];
        $this->assertSame($expected, $actual);

        $expected = 'NamedMultipleParams';
        $actual   = $parsing['methods']['testMethod1'][4]['name'];
        $this->assertSame($expected, $actual);

        // Comment without content
        $actual = isset($parsing['methods']['testMethod2']);
        $this->assertFalse($actual);

        // Same line annotations
        $expected = 3;
        $actual   = $parsing['methods']['testMethod3'];
        $this->assertCount($expected, $actual);

        $expected = 'Simple';
        $actual   = $parsing['methods']['testMethod3'][0]['name'];
        $this->assertSame($expected, $actual);

        $expected = 'SingleParam';
        $actual   = $parsing['methods']['testMethod3'][1]['name'];
        $this->assertSame($expected, $actual);

        $expected = 'MultipleParams';
        $actual   = $parsing['methods']['testMethod3'][2]['name'];
        $this->assertSame($expected, $actual);

        // Unordered annotations
        $expected = 3;
        $actual   = $parsing['methods']['testMethod4'];
        $this->assertCount($expected, $actual);

        $expected = 'Simple';
        $actual   = $parsing['methods']['testMethod4'][0]['name'];
        $this->assertSame($expected, $actual);

        $expected = 'SingleParam';
        $actual   = $parsing['methods']['testMethod4'][1]['name'];
        $this->assertSame($expected, $actual);

        $expected = 'MultipleParams';
        $actual   = $parsing['methods']['testMethod4'][2]['name'];
        $this->assertSame($expected, $actual);


        // Unordered annotations + extra content
        $expected = 3;
        $actual   = $parsing['methods']['testMethod5'];
        $this->assertCount($expected, $actual);

        $expected = 'Simple';
        $actual   = $parsing['methods']['testMethod5'][0]['name'];
        $this->assertSame($expected, $actual);

        $expected = 'SingleParam';
        $actual   = $parsing['methods']['testMethod5'][1]['name'];
        $this->assertSame($expected, $actual);

        $expected = 'MultipleParams';
        $actual   = $parsing['methods']['testMethod5'][2]['name'];
        $this->assertSame($expected, $actual);
    }
}

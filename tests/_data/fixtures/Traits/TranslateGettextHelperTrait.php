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

namespace Phalcon\Tests\Fixtures\Traits;

use Phalcon\Translate\Adapter\Gettext;
use Phalcon\Translate\Exception;
use Phalcon\Translate\InterpolatorFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

trait TranslateGettextHelperTrait
{
    /**
     * Tests Phalcon\Translate\Adapter\Gettext :: query() - array access and
     * UTF8 strings
     *
     * @throws Exception
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    #[Test]
    public function testTranslateAdapterGettextWithArrayAccessAndUTF8Strings()
    {
        $language = $this->getGettextConfig();

        $translator = new Gettext(new InterpolatorFactory(), $language);

        $vars = [
            'fname' => 'John',
            'lname' => 'Doe',
            'mname' => 'D.',
        ];

        $expected = 'Привет, John D. Doe!';
        $actual   = $translator->{$this->func()}('Привет, %fname% %mname% %lname%!', $vars);
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Translate\Adapter\Gettext :: query()
     *
     * @throws Exception
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2020-09-09
     */
    #[Test]
    #[DataProvider('getQueryProvider')]
    public function translateAdapterGettextQuery(
        array $tests
    ): void {
        $language   = $this->getGettextConfig();
        $translator = new Gettext(new InterpolatorFactory(), $language);

        foreach ($tests as $key => $expected) {
            $actual = $translator->{$this->func()}($key);
            $this->assertSame($expected, $actual);
        }
    }

    /**
     * Tests Phalcon\Translate\Adapter\Gettext :: query() -
     * variable substitution in string with no variables
     *
     * @throws Exception
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2020-09-09
     */
    #[Test]
    #[DataProvider('getQueryProvider')]
    public function translateAdapterGettextVariableSubstitutionNoVariables(
        array $tests
    ): void {
        $language   = $this->getGettextConfig();
        $translator = new Gettext(new InterpolatorFactory(), $language);

        foreach ($tests as $key => $expected) {
            $actual = $translator->{$this->func()}(
                $key,
                [
                    'name' => 'my friend',
                ]
            );
            $this->assertSame($expected, $actual);
        }
    }

    /**
     * Tests Phalcon\Translate\Adapter\Gettext :: query() -
     * variable substitution in string (one variable)
     *
     * @throws Exception
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2020-09-09
     */
    #[Test]
    #[DataProvider('getQueryOneVariable')]
    public function translateAdapterGettextVariableSubstitutionOneVariable(
        array $tests
    ): void {
        $language   = $this->getGettextConfig();
        $translator = new Gettext(new InterpolatorFactory(), $language);

        foreach ($tests as $key => $expected) {
            $actual = $translator->{$this->func()}($key, ['name' => 'my friend']);
            $this->assertSame($expected, $actual);
        }
    }

    /**
     * Tests Phalcon\Translate\Adapter\Gettext :: query() -
     * variable substitution in string (two variables)
     *
     * @throws Exception
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2020-09-09
     */
    #[Test]
    #[DataProvider('getQueryTwoVariables')]
    public function translateAdapterGettextVariableSubstitutionTwoVariable(
        array $tests
    ): void {
        $language   = $this->getGettextConfig();
        $translator = new Gettext(new InterpolatorFactory(), $language);

        $vars = [
            'song'   => 'Dust in the wind',
            'artist' => 'Kansas',
        ];

        foreach ($tests as $key => $expected) {
            $actual = $translator->{$this->func()}($key, $vars);
            $this->assertSame($expected, $actual);
        }
    }

    /**
     * @return string
     */
    abstract protected function func(): string;

    /**
     * @return array
     */
    abstract protected function getGettextConfig(): array;

    /**
     * Data provider for the query one variable substitution
     *
     * @return array[]
     */
    public static function getQueryOneVariable(): array
    {
        return [
            [
                [
                    'hello-key' => 'Hello my friend',
                ],
            ],
        ];
    }

    /**
     * Data provider for the query tests
     *
     * @return array[]
     */
    public static function getQueryProvider(): array
    {
        return [
            [
                [
                    'hi'  => 'Hello',
                    'bye' => 'Bye',
                ],
            ],
        ];
    }

    /**
     * Data provider for the query one variable substitution
     *
     * @return array[]
     */
    public static function getQueryTwoVariables(): array
    {
        return [
            [
                [
                    'song-key' => 'The song is Dust in the wind (Kansas)',
                ],
            ],
        ];
    }
}

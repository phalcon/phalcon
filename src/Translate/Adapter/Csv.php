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

namespace Phalcon\Translate\Adapter;

use ArrayAccess;
use Phalcon\Support\Traits\PhpFileTrait;
use Phalcon\Translate\Exception;
use Phalcon\Translate\InterpolatorFactory;

use function is_resource;

/**
 * Class Csv
 *
 * @package Phalcon\Translate\Adapter
 *
 * @property array $translate
 */
class Csv extends AbstractAdapter implements ArrayAccess
{
    use PhpFileTrait;

    /**
     * @var array
     */
    protected array $translate = [];

    /**
     * Csv constructor.
     *
     * @param InterpolatorFactory $interpolator
     * @param array               $options = [
     *                                     'content'   => '',
     *                                     'delimiter' => ';',
     *                                     'enclosure' => '"'
     *                                     ]
     *
     * @throws Exception
     */
    public function __construct(
        InterpolatorFactory $interpolator,
        array               $options
    ) {
        parent::__construct($interpolator, $options);

        if (true !== isset($options['content'])) {
            throw new Exception('Parameter "content" is required');
        }

        $delimiter = $options['delimiter'] ?? ';';
        $enclosure = $options['enclosure'] ?? "\"";

        $this->load($options['content'], 0, $delimiter, $enclosure);
    }

    /**
     * Check whether is defined a translation key in the internal array
     *
     * @param string $index
     *
     * @return bool
     */
    public function exists(string $index): bool
    {
        return isset($this->translate[$index]);
    }

    /**
     * Returns the translation related to the given key
     *
     * @param string $index
     * @param array  $placeholders
     *
     * @return string
     */
    public function query(string $index, array $placeholders = []): string
    {
        $translation = $this->translate[$index] ?? $index;

        return $this->replacePlaceholders($translation, $placeholders);
    }

    /**
     * Load translates from file
     *
     * @param string $file
     * @param int    $length
     * @param string $separator
     * @param string $enclosure
     *
     * @throws Exception
     */
    private function load(
        string $file,
        int    $length,
        string $separator,
        string $enclosure
    ): void {
        $pointer = $this->phpFopen($file, 'rb');

        if (true !== is_resource($pointer)) {
            throw new Exception(
                'Error opening translation file "' . $file . '"'
            );
        }

        while (true) {
            $data = $this->phpFgetCsv($pointer, $length, $separator, $enclosure);

            if (false === $data) {
                break;
            }

            if (
                '#' === substr($data[0], 0, 1) ||
                true !== isset($data[1])
            ) {
                continue;
            }

            $this->translate[$data[0]] = $data[1];
        }

        fclose($pointer);
    }
}

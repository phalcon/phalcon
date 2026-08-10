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

use Phalcon\Contracts\Translate\TranslateTypes;
use Phalcon\Traits\Php\FileTrait;
use Phalcon\Translate\Exception;
use Phalcon\Translate\Exceptions\FileOpenError;
use Phalcon\Translate\Exceptions\MissingRequiredParameter;
use Phalcon\Translate\InterpolatorFactory;

use function is_resource;

/**
 * @phpstan-import-type translate_csv_options from TranslateTypes
 * @phpstan-import-type translate_data from TranslateTypes
 * @phpstan-import-type translate_placeholders from TranslateTypes
 *
 * @extends AbstractAdapter<string, string>
 */
class Csv extends AbstractAdapter
{
    use FileTrait;

    /**
     * @phpstan-var translate_data
     */
    protected array $translate = [];

    /**
     * Csv constructor.
     *
     * @phpstan-param translate_csv_options $options
     *
     * @throws Exception
     */
    public function __construct(
        InterpolatorFactory $interpolator,
        array $options
    ) {
        parent::__construct($interpolator, $options);

        if (!isset($options['content'])) {
            throw new MissingRequiredParameter('content');
        }

        $delimiter = $options['delimiter'] ?? ';';
        $enclosure = $options['enclosure'] ?? "\"";
        $escape    = $options['escape'] ?? "\\";

        $this->load($options['content'], 0, $delimiter, $enclosure, $escape);
    }

    /**
     * Check whether is defined a translation key in the internal array
     *
     * @param string $index
     *
     * @return bool
     * @deprecated
     */
    public function exists(string $index): bool
    {
        return $this->has($index);
    }

    /**
     * Check whether is defined a translation key in the internal array
     *
     * @param string $index
     *
     * @return bool
     */
    public function has(string $index): bool
    {
        return isset($this->translate[$index]);
    }

    /**
     * Returns the translation related to the given key
     *
     * @phpstan-param translate_placeholders $placeholders
     *
     * @return string
     * @throws Exception
     */
    public function query(string $translateKey, array $placeholders = []): string
    {
        $translation = $this->translate[$translateKey] ?? $this->notFound($translateKey);

        return $this->replacePlaceholders($translation, $placeholders);
    }

    /**
     * Returns the internal array
     *
     * @phpstan-return translate_data
     */
    public function toArray(): array
    {
        return $this->translate;
    }

    /**
     * Load translations from file
     *
     * Lines whose first column begins with a `#` are treated as comments
     * and skipped.
     *
     * @phpstan-param int<0, max> $length
     *
     * @throws FileOpenError
     */
    private function load(
        string $file,
        int $length,
        string $delimiter,
        string $enclosure,
        string $escape
    ): void {
        $pointer = $this->phpFopen($file, 'rb');

        if (true !== is_resource($pointer)) {
            throw new FileOpenError($file);
        }

        while (true) {
            /** @var array<array-key, string>|false $data */
            $data = $this->phpFgetCsv($pointer, $length, $delimiter, $enclosure, $escape);

            if (false === $data) {
                break;
            }

            if (str_starts_with($data[0], '#') || !isset($data[1])) {
                continue;
            }

            $this->translate[$data[0]] = $data[1];
        }

        $this->phpFclose($pointer);
    }
}

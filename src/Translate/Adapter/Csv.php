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

use Exception as BaseException;
use Phalcon\Traits\Php\FileTrait;
use Phalcon\Translate\Exception;
use Phalcon\Translate\Exceptions\FileOpenError;
use Phalcon\Translate\Exceptions\MissingRequiredParameter;
use Phalcon\Translate\InterpolatorFactory;

use function is_resource;

/**
 * @phpstan-type TOptions array{
 *      content?: string,
 *      delimiter?: string,
 *      enclosure?: string,
 *      escape?: string
 * }
 */
class Csv extends AbstractAdapter
{
    use FileTrait;

    /**
     * @var array<string, string>
     */
    protected array $translate = [];

    /**
     * Csv constructor.
     *
     * @phpstan-param TOptions            $options
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
     */
    public function has(string $index): bool
    {
        return isset($this->translate[$index]);
    }

    /**
     * Returns the translation related to the given key
     *
     * @phpstan-param array<string, string> $placeholders
     *
     * @return string
     * @throws BaseException
     */
    public function query(string $translateKey, array $placeholders = []): string
    {
        $translation = $this->translate[$translateKey] ?? $this->notFound($translateKey);

        return $this->replacePlaceholders($translation, $placeholders);
    }

    /**
     * Returns the internal array
     *
     * @return array<string, string>
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
     * @param string $file
     * @param int    $length
     * @param string $separator
     * @param string $enclosure
     * @param string $escape
     *
     * @return void
     * @throws FileOpenError
     */
    private function load(
        string $file,
        int $length,
        string $separator,
        string $enclosure,
        string $escape
    ): void {
        $pointer = $this->phpFopen($file, 'rb');

        if (true !== is_resource($pointer)) {
            throw new FileOpenError($file);
        }

        while (true) {
            /** @var array<array-key, string>|false $data */
            $data = $this->phpFgetCsv($pointer, $length, $separator, $enclosure, $escape);

            if (false === $data) {
                break;
            }

            if (str_starts_with($data[0], '#') || !isset($data[1])) {
                continue;
            }

            $this->translate[$data[0]] = $data[1];
        }

        fclose($pointer);
    }
}

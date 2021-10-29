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
use Phalcon\Traits\Php\InfoTrait;
use Phalcon\Translate\Exception;
use Phalcon\Translate\InterpolatorFactory;

use function array_merge;
use function bindtextdomain;
use function dngettext;
use function gettext;
use function is_array;
use function ngettext;
use function putenv;
use function setlocale;
use function textdomain;

use const LC_ALL;

/**
 * Phalcon\Translate\Adapter\Gettext
 *
 * ```php
 * use Phalcon\Translate\Adapter\Gettext;
 *
 * $adapter = new Gettext(
 *     [
 *         "locale"        => "de_DE.UTF-8",
 *         "defaultDomain" => "translations",
 *         "directory"     => "/path/to/application/locales",
 *         "category"      => LC_MESSAGES,
 *     ]
 * );
 * ```
 *
 * Allows translations using gettext
 *
 * @property int          $category
 * @property string       $defaultDomain
 * @property string|array $directory
 * @property string|false $locale
 */
class Gettext extends AbstractAdapter implements ArrayAccess
{
    use InfoTrait;

    /**
     * @var int
     */
    protected int $category;

    /**
     * @var string
     */
    protected string $defaultDomain;

    /**
     * @var string|array
     */
    protected $directory;

    /**
     * @var string|false
     */
    protected $locale;

    /**
     * Gettext constructor.
     *
     * @param InterpolatorFactory $interpolator
     * @param array               $options = [
     *                                       'locale'        => '',
     *                                       'defaultDomain' => '',
     *                                       'directory'     => '',
     *                                       'category'      => ''
     *                                       ]
     *
     * @throws Exception
     */
    public function __construct(
        InterpolatorFactory $interpolator,
        array $options
    ) {
        if (true !== $this->phpFunctionExists('gettext')) {
            throw new Exception(
                'This class requires the gettext extension for PHP'
            );
        }

        parent::__construct($interpolator, $options);

        $this->prepareOptions($options);
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
        $result = $this->query($index);

        return ($result !== $index);
    }

    /**
     * @return int
     */
    public function getCategory(): int
    {
        return $this->category;
    }

    /**
     * @return string
     */
    public function getDefaultDomain(): string
    {
        return $this->defaultDomain;
    }

    /**
     * @return array|string
     */
    public function getDirectory()
    {
        return $this->directory;
    }

    /**
     * @return string|false
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * The plural version of gettext().
     * Some languages have more than one form for plural messages dependent on
     * the count.
     *
     * @param string      $msgid1
     * @param string      $msgid2
     * @param int         $count
     * @param array       $placeholders
     * @param string|null $domain
     *
     * @return string
     */
    public function nquery(
        string $msgid1,
        string $msgid2,
        int $count,
        array $placeholders = [],
        string $domain = null
    ): string {

        if (null === $domain) {
            $translation = ngettext($msgid1, $msgid2, $count);
        } else {
            $translation = dngettext($domain, $msgid1, $msgid2, $count);
        }

        return $this->replacePlaceholders($translation, $placeholders);
    }

    /**
     * Returns the translation related to the given key.
     *
     * ```php
     * $translator->query("你好 %name%！", ["name" => "Phalcon"]);
     * ```
     *
     * @param string $translateKey
     * @param array  $placeholders
     *
     * @return string
     */
    public function query(string $translateKey, array $placeholders = []): string
    {
        $translation = gettext($translateKey);

        return $this->replacePlaceholders($translation, $placeholders);
    }

    /**
     * Sets the default domain
     *
     * @return string
     */
    public function resetDomain(): string
    {
        return textdomain($this->getDefaultDomain());
    }

    /**
     * Sets the domain default to search within when calls are made to gettext()
     *
     * @param string $domain
     */
    public function setDefaultDomain(string $domain): void
    {
        $this->defaultDomain = $domain;
    }

    /**
     * Sets the path for a domain
     *
     * ```php
     * // Set the directory path
     * $gettext->setDirectory("/path/to/the/messages");
     *
     * // Set the domains and directories path
     * $gettext->setDirectory(
     *     [
     *         "messages" => "/path/to/the/messages",
     *         "another"  => "/path/to/the/another",
     *     ]
     * );
     * ```
     *
     * @param string|array $directory
     */
    public function setDirectory($directory): void
    {
        if (true !== empty($directory)) {
            $this->directory = $directory;

            if (true === is_array($directory)) {
                foreach ($directory as $index => $item) {
                    bindtextdomain($index, $item);
                }
            } else {
                bindtextdomain(
                    $this->getDefaultDomain(),
                    $directory
                );
            }
        }
    }

    /**
     * Changes the current domain (i.e. the translation file)
     *
     * @param string|null $domain
     *
     * @return string
     */
    public function setDomain(?string $domain = null): string
    {
        return textdomain($domain);
    }

    /**
     * Sets locale information
     *
     * ```php
     * // Set locale to Dutch
     * $gettext->setLocale(LC_ALL, "nl_NL");
     *
     * // Try different possible locale names for German
     * $gettext->setLocale(LC_ALL, "de_DE@euro", "de_DE", "de", "ge");
     * ```
     *
     * @param int   $category
     * @param array $localeArray
     *
     * @return false|string
     */
    public function setLocale(int $category, array $localeArray = [])
    {
        $this->category = $category;
        $this->locale   = setlocale($category, $localeArray);

        putenv("LC_ALL=" . $this->locale);
        putenv("LANG=" . $this->locale);
        putenv("LANGUAGE=" . $this->locale);
        setlocale(LC_ALL, $this->locale);

        return $this->locale;
    }

    /**
     * Gets default options
     *
     * @return array
     */
    protected function getOptionsDefault(): array
    {
        return [
            'category'      => LC_ALL,
            'defaultDomain' => 'messages',
        ];
    }

    /**
     * Validator for constructor
     *
     * @param array $options
     *
     * @throws Exception
     */
    protected function prepareOptions(array $options): void
    {
        if (true !== isset($options['locale'])) {
            throw new Exception("Parameter 'locale' is required");
        }

        if (true !== isset($options['directory'])) {
            throw new Exception("Parameter 'directory' is required");
        }

        $options = array_merge($this->getOptionsDefault(), $options);

        $this->setLocale($options['category'], $options['locale']);
        $this->setDefaultDomain($options['defaultDomain']);
        $this->setDirectory($options['directory']);
        $this->setDomain($options['defaultDomain']);
    }
}

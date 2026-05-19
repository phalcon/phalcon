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

namespace Phalcon\Html;

use Phalcon\Html\Escaper\AttributeEscaper;
use Phalcon\Html\Escaper\CssEscaper;
use Phalcon\Html\Escaper\EscaperInterface;
use Phalcon\Html\Escaper\HtmlEscaper;
use Phalcon\Html\Escaper\JsEscaper;
use Phalcon\Html\Escaper\UrlEscaper;

use const ENT_HTML401;
use const ENT_QUOTES;
use const ENT_SUBSTITUTE;

/**
 * Phalcon\Html\Escaper
 *
 * Escapes different kinds of text securing them. By using this component you
 * may prevent XSS attacks.
 *
 * The class is a façade over five per-context escapers (`HtmlEscaper`,
 * `AttributeEscaper`, `CssEscaper`, `JsEscaper`, `UrlEscaper`). Each can be
 * retrieved via the matching `getXxxEscaper()` accessor and substituted via
 * the matching `setXxxEscaper()` setter. The legacy `setEncoding`,
 * `setFlags`, and `setDoubleEncode` continue to fan out to all sub-objects
 * so existing code keeps working.
 *
 * This component only works with UTF-8. The PREG extension needs to be compiled
 * with UTF-8 support.
 *
 *```php
 * $escaper = new \Phalcon\Html\Escaper();
 *
 * $escaped = $escaper->css("font-family: <Verdana>");
 *
 * echo $escaped; // font\2D family\3A \20 \3C Verdana\3E
 *```
 *
 * @property AttributeEscaper $attributeEscaper
 * @property CssEscaper       $cssEscaper
 * @property HtmlEscaper      $htmlEscaper
 * @property JsEscaper        $jsEscaper
 * @property UrlEscaper       $urlEscaper
 */
class Escaper implements EscaperInterface
{
    /**
     * @var AttributeEscaper
     */
    protected AttributeEscaper $attributeEscaper;

    /**
     * @var CssEscaper
     */
    protected CssEscaper $cssEscaper;

    /**
     * @var HtmlEscaper
     */
    protected HtmlEscaper $htmlEscaper;

    /**
     * @var JsEscaper
     */
    protected JsEscaper $jsEscaper;

    /**
     * @var UrlEscaper
     */
    protected UrlEscaper $urlEscaper;

    /**
     * Constructor. Accepts the legacy scalar params for backward compatibility
     * and fans them out to every sub-escaper so existing code keeps working.
     *
     * @param string $encoding
     * @param int    $flags
     * @param bool   $doubleEncode
     */
    public function __construct(
        string $encoding = 'utf-8',
        int $flags = ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401,
        bool $doubleEncode = true
    ) {
        $this->attributeEscaper = new AttributeEscaper();
        $this->cssEscaper       = new CssEscaper();
        $this->htmlEscaper      = new HtmlEscaper();
        $this->jsEscaper        = new JsEscaper();
        $this->urlEscaper       = new UrlEscaper();

        if ('utf-8' !== $encoding) {
            $this->setEncoding($encoding);
        }

        if ((ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401) !== $flags) {
            $this->setFlags($flags);
        }

        if (true !== $doubleEncode) {
            $this->setDoubleEncode($doubleEncode);
        }
    }

    /**
     * Escapes a HTML attribute string or array. Delegates to `AttributeEscaper`.
     *
     * @param array|string|null $input
     *
     * @return string
     */
    public function attributes(mixed $input = null): string
    {
        return $this->attributeEscaper->escape($input);
    }

    /**
     * Escape CSS strings. Delegates to `CssEscaper`.
     *
     * @param string $input
     *
     * @return string
     */
    public function css(string $input): string
    {
        return $this->cssEscaper->escape($input);
    }

    /**
     * Detects the character encoding of a string. Delegates to `HtmlEscaper`.
     *
     * @param string $input
     *
     * @return string|null
     */
    final public function detectEncoding(string $input): string | null
    {
        return $this->htmlEscaper->detectEncoding($input);
    }

    /**
     * @param string $input
     *
     * @return string
     *
     * @deprecated
     */
    public function escapeCss(string $input): string
    {
        return $this->css($input);
    }

    /**
     * @param string $input
     *
     * @return string
     *
     * @deprecated
     */
    public function escapeHtml(string $input = ''): string
    {
        return $this->html($input);
    }

    /**
     * @param string $input
     *
     * @return string
     *
     * @deprecated
     */
    public function escapeHtmlAttr(string $input = ''): string
    {
        return $this->attributes($input);
    }

    /**
     * @param string $input
     *
     * @return string
     *
     * @deprecated
     */
    public function escapeJs(string $input): string
    {
        return $this->js($input);
    }

    /**
     * @param string $input
     *
     * @return string
     *
     * @deprecated
     */
    public function escapeUrl(string $input): string
    {
        return $this->url($input);
    }

    /**
     * @return AttributeEscaper
     */
    public function getAttributeEscaper(): AttributeEscaper
    {
        return $this->attributeEscaper;
    }

    /**
     * @return CssEscaper
     */
    public function getCssEscaper(): CssEscaper
    {
        return $this->cssEscaper;
    }

    /**
     * Returns the encoding from the HtmlEscaper.
     *
     * @return string
     */
    public function getEncoding(): string
    {
        return $this->htmlEscaper->getEncoding();
    }

    /**
     * Returns the flags from the HtmlEscaper.
     *
     * @return int
     */
    public function getFlags(): int
    {
        return $this->htmlEscaper->getFlags();
    }

    /**
     * @return HtmlEscaper
     */
    public function getHtmlEscaper(): HtmlEscaper
    {
        return $this->htmlEscaper;
    }

    /**
     * @return JsEscaper
     */
    public function getJsEscaper(): JsEscaper
    {
        return $this->jsEscaper;
    }

    /**
     * @return UrlEscaper
     */
    public function getUrlEscaper(): UrlEscaper
    {
        return $this->urlEscaper;
    }

    /**
     * Escapes a HTML string. Delegates to `HtmlEscaper`.
     *
     * @param string|null $input
     *
     * @return string
     */
    public function html(string | null $input = null): string
    {
        return $this->htmlEscaper->escape($input);
    }

    /**
     * Escape javascript strings. Delegates to `JsEscaper`.
     *
     * @param string $input
     *
     * @return string
     */
    public function js(string $input): string
    {
        return $this->jsEscaper->escape($input);
    }

    /**
     * Normalizes a string's encoding to UTF-32. Delegates to `HtmlEscaper`.
     *
     * @param string $input
     *
     * @return string
     */
    final public function normalizeEncoding(string $input): string
    {
        return $this->htmlEscaper->normalizeEncoding($input);
    }

    /**
     * @param AttributeEscaper $escaper
     *
     * @return Escaper
     */
    public function setAttributeEscaper(AttributeEscaper $escaper): static
    {
        $this->attributeEscaper = $escaper;

        return $this;
    }

    /**
     * @param CssEscaper $escaper
     *
     * @return Escaper
     */
    public function setCssEscaper(CssEscaper $escaper): static
    {
        $this->cssEscaper = $escaper;

        return $this;
    }

    /**
     * Sets the double_encode flag. Fans out to all sub-escapers.
     *
     *```php
     * $escaper->setDoubleEncode(false);
     *```
     *
     * @param bool $doubleEncode
     *
     * @return Escaper
     */
    public function setDoubleEncode(bool $doubleEncode): static
    {
        $this->attributeEscaper->setDoubleEncode($doubleEncode);
        $this->cssEscaper->setDoubleEncode($doubleEncode);
        $this->htmlEscaper->setDoubleEncode($doubleEncode);
        $this->jsEscaper->setDoubleEncode($doubleEncode);
        $this->urlEscaper->setDoubleEncode($doubleEncode);

        return $this;
    }

    /**
     * Sets the encoding. Fans out to all sub-escapers.
     *
     *```php
     * $escaper->setEncoding("utf-8");
     *```
     *
     * @param string $encoding
     *
     * @return EscaperInterface
     */
    public function setEncoding(string $encoding): static
    {
        $this->attributeEscaper->setEncoding($encoding);
        $this->cssEscaper->setEncoding($encoding);
        $this->htmlEscaper->setEncoding($encoding);
        $this->jsEscaper->setEncoding($encoding);
        $this->urlEscaper->setEncoding($encoding);

        return $this;
    }

    /**
     * Sets the htmlspecialchars flags. Fans out to all sub-escapers.
     *
     *```php
     * $escaper->setFlags(ENT_XHTML);
     *```
     *
     * @param int $flags
     *
     * @return EscaperInterface
     */
    public function setFlags(int $flags): static
    {
        $this->attributeEscaper->setFlags($flags);
        $this->cssEscaper->setFlags($flags);
        $this->htmlEscaper->setFlags($flags);
        $this->jsEscaper->setFlags($flags);
        $this->urlEscaper->setFlags($flags);

        return $this;
    }

    /**
     * @param HtmlEscaper $escaper
     *
     * @return Escaper
     */
    public function setHtmlEscaper(HtmlEscaper $escaper): static
    {
        $this->htmlEscaper = $escaper;

        return $this;
    }

    /**
     * Sets the HTML quoting type for htmlspecialchars.
     *
     *```php
     * $escaper->setHtmlQuoteType(ENT_XHTML);
     *```
     *
     * @param int $flags
     *
     * @return EscaperInterface
     *
     * @deprecated
     */
    public function setHtmlQuoteType(int $flags): static
    {
        return $this->setFlags($flags);
    }

    /**
     * @param JsEscaper $escaper
     *
     * @return Escaper
     */
    public function setJsEscaper(JsEscaper $escaper): static
    {
        $this->jsEscaper = $escaper;

        return $this;
    }

    /**
     * @param UrlEscaper $escaper
     *
     * @return Escaper
     */
    public function setUrlEscaper(UrlEscaper $escaper): static
    {
        $this->urlEscaper = $escaper;

        return $this;
    }

    /**
     * Escapes a URL. Delegates to `UrlEscaper`.
     *
     * @param string $input
     *
     * @return string
     */
    public function url(string $input): string
    {
        return $this->urlEscaper->escape($input);
    }
}

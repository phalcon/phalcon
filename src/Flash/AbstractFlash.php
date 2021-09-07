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

namespace Phalcon\Flash;

use Phalcon\Di\InjectionAwareInterface;
use Phalcon\Di\Traits\InjectionAwareTrait;
use Phalcon\Flash\Traits\FlashGettersTrait;
use Phalcon\Html\EscaperInterface;
use Phalcon\Session\ManagerInterface as SessionInterface;
use Phalcon\Support\Helper\Str\Traits\InterpolateTrait;

/**
 * Shows HTML notifications related to different circumstances. Classes can be
 * stylized using CSS
 *
 *```php
 * $flash->success("The record was successfully deleted");
 * $flash->error("Cannot open the file");
 *```
 *
 * Class AbstractFlash
 *
 * @package Phalcon\Flash
 */
abstract class AbstractFlash implements FlashInterface, InjectionAwareInterface
{
    use FlashGettersTrait;
    use InjectionAwareTrait;
    use InterpolateTrait;

    /**
     * AbstractFlash constructor.
     *
     * @param EscaperInterface|null $escaper
     * @param SessionInterface|null $session
     */
    public function __construct(
        EscaperInterface $escaper = null,
        SessionInterface $session = null
    ) {
        $this->escaperService = $escaper;
        $this->sessionService = $session;

        $this->cssClasses = [
            'error'   => 'errorMessage',
            'notice'  => 'noticeMessage',
            'success' => 'successMessage',
            'warning' => 'warningMessage',
        ];
    }

    /**
     * Shows a HTML error message
     *
     *```php
     * $flash->error("This is an error");
     *```
     *
     * @param string $message
     *
     * @return string|null
     */
    public function error(string $message): ?string
    {
        return $this->message('error', $message);
    }

    /**
     * Returns the Escaper Service
     *
     * @return EscaperInterface
     * @throws Exception
     */
    public function getEscaperService(): EscaperInterface
    {
        if (null !== $this->escaperService) {
            return $this->escaperService;
        }

        if (
            null !== $this->container &&
            true === $this->container->has('escaper')
        ) {
            $this->escaperService = $this->container->getShared('escaper');

            return $this->escaperService;
        }

        throw new Exception(
            'A dependency injection container is required to access ' .
            'the "escaper" service'
        );
    }

    /**
     * Shows a HTML notice/information message
     *
     *```php
     * $flash->notice("This is an information");
     *```
     *
     * @param string $message
     *
     * @return string|null
     */
    public function notice(string $message): ?string
    {
        return $this->message('notice', $message);
    }

    /**
     * Set the autoescape mode in generated HTML
     *
     * @param bool $autoescape
     *
     * @return $this
     */
    public function setAutoescape(bool $autoescape): AbstractFlash
    {
        $this->autoescape = $autoescape;

        return $this;
    }

    /**
     * Set if the output must be implicitly formatted with HTML
     *
     * @param bool $automaticHtml
     *
     * @return $this
     */
    public function setAutomaticHtml(bool $automaticHtml): AbstractFlash
    {
        $this->automaticHtml = $automaticHtml;

        return $this;
    }

    /**
     * Set an array with CSS classes to format the messages
     *
     * @param array $cssClasses
     *
     * @return $this
     */
    public function setCssClasses(array $cssClasses): AbstractFlash
    {
        $this->cssClasses = $cssClasses;

        return $this;
    }

    /**
     * Set an custom template for showing the messages
     *
     * @param string $customTemplate
     *
     * @return $this
     */
    public function setCustomTemplate(string $customTemplate): AbstractFlash
    {
        $this->customTemplate = $customTemplate;

        return $this;
    }

    /**
     * Sets the Escaper Service
     *
     * @param EscaperInterface $escaperService
     *
     * @return $this
     */
    public function setEscaperService(EscaperInterface $escaperService): AbstractFlash
    {
        $this->escaperService = $escaperService;

        return $this;
    }

    /**
     * Set whether the output must be implicitly flushed to the output or
     * returned as string
     *
     * @param bool $implicitFlush
     *
     * @return $this
     */
    public function setImplicitFlush(bool $implicitFlush): AbstractFlash
    {
        $this->implicitFlush = $implicitFlush;

        return $this;
    }

    /**
     * Shows a HTML success message
     *
     *```php
     * $flash->success("The process was finished successfully");
     *```
     *
     * @param string $message
     *
     * @return string|null
     */
    public function success(string $message): ?string
    {
        return $this->message('success', $message);
    }

    /**
     * Outputs a message formatting it with HTML
     *
     *```php
     * $flash->outputMessage("error", $message);
     *```
     *
     * @param string $type
     * @param mixed  $message
     *
     * @return string|null
     * @throws Exception
     */
    public function outputMessage(string $type, $message): ?string
    {
        $content = '';

        if (true !== is_array($message) && true !== is_string($message)) {
            throw new Exception('The message must be an array or a string');
        }

        /**
         * Make this an array. Same code processes string and array
         */
        if (true !== is_array($message)) {
            $message = [$message];
        }

        if (true !== $this->implicitFlush) {
            $content = '';
        }

        foreach ($message as $item) {
            $prepared = $this->prepareEscapedMessage($item);
            $html     = $this->prepareHtmlMessage($type, $prepared);

            if (true === $this->implicitFlush) {
                echo $html;
            } else {
                $content          .= $html;
                $this->messages[] = $html;
            }
        }

        /**
         * We return the message as a string if the implicitFlush is turned
         * off
         */
        if (true !== $this->implicitFlush) {
            return $content;
        }

        return null;
    }

    /**
     * Shows a HTML warning message
     *
     *```php
     * $flash->warning("Hey, this is important");
     *```
     *
     * @param string $message
     *
     * @return string|null
     */
    public function warning(string $message): ?string
    {
        return $this->message('warning', $message);
    }

    /**
     * @param string $cssClassses
     *
     * @return string
     */
    private function getTemplate(string $cssClassses): string
    {
        if ('' === $this->customTemplate) {
            if ('' === $cssClassses) {
                return '<div>{message}</div>' . PHP_EOL;
            }

            return '<div class="{cssClass}">{message}</div>' . PHP_EOL;
        }

        return $this->customTemplate;
    }

    /**
     * Returns the message escaped if the autoEscape is true, otherwise the
     * original message is returned
     *
     * @param string $message
     *
     * @return string
     * @throws Exception
     */
    private function prepareEscapedMessage(string $message): string
    {
        if (true !== $this->autoescape) {
            return $message;
        }

        $escaper = $this->getEscaperService();

        return $escaper->html($message);
    }

    /**
     * Prepares the HTML output for the message. If automaticHtml is not set
     * then the original message is returned
     *
     * @param string $type
     * @param string $message
     *
     * @return string
     */
    private function prepareHtmlMessage(string $type, string $message): string
    {
        if (true !== $this->automaticHtml) {
            return $message;
        }

        $replaceCss = $this->cssClasses[$type] ?? '';
        if (true !== empty($replaceCss)) {
            if (true !== is_array($replaceCss)) {
                $replaceCss = [$replaceCss];
            }

            $replaceCss = join(' ', $replaceCss);
        }

        return $this->toInterpolate(
            $this->getTemplate($replaceCss),
            [
                'cssClass' => $replaceCss,
                'message'  => $message,
            ]
        );
    }
}

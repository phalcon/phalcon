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
use Phalcon\Html\Escaper\EscaperInterface;
use Phalcon\Session\ManagerInterface as SessionInterface;
use Phalcon\Traits\Helper\Str\InterpolateTrait;

use function is_array;
use function is_string;

use const PHP_EOL;

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
        EscaperInterface | null $escaper = null,
        SessionInterface | null $session = null
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
    public function error(string $message): string | null
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

        $this->checkContainer(
            Exception::class,
            "the 'escaper' service"
        );

        if (true !== $this->container->has('escaper')) {
            $this->checkContainer(
                Exception::class,
                "the 'escaper' service"
            );
        }

        if (null === $this->escaperService) {
            $this->escaperService = $this->container->getShared('escaper');
        }


        return $this->escaperService;
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
    public function notice(string $message): string | null
    {
        return $this->message('notice', $message);
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
    public function outputMessage(string $type, $message): string | null
    {
        $content = '';

        if (!is_array($message) && !is_string($message)) {
            throw new Exception('The message must be an array or a string');
        }

        /**
         * Make this an array. Same code processes string and array
         */
        if (!is_array($message)) {
            $message = [$message];
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
     * Set an array with CSS classes to format the icon messages
     *
     * @param array $cssIconClasses
     *
     * @return $this
     */
    public function setCssIconClasses(array $cssIconClasses): AbstractFlash
    {
        $this->cssIconClasses = $cssIconClasses;

        return $this;
    }

    /**
     * Set a custom template for showing the messages
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
    public function success(string $message): string | null
    {
        return $this->message('success', $message);
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
    public function warning(string $message): string | null
    {
        return $this->message('warning', $message);
    }

    /**
     * Checks the collection and returns the content as a string
     * (array is joined)
     *
     * @param array  $collection
     * @param string $type
     *
     * @return string
     */
    private function checkClasses(array $collection, string $type): string
    {
        $content = $collection[$type] ?? '';

        if (!empty($content)) {
            if (!is_array($content)) {
                $content = [$content];
            }

            $content = implode(' ', $content);
        }

        return $content;
    }

    /**
     * Returns the template for the CSS classes (with icon classes). It will
     * either be the custom one (defined) or the default
     *
     * @param string $cssClasses
     * @param string $cssIconClasses
     *
     * @return string
     */
    private function getTemplate(string $cssClasses, string $cssIconClasses): string
    {
        $template   = "<div%divString%>%iconString%%message%</div>" . PHP_EOL;
        $divString  = "";
        $iconString = "";

        if (!empty($this->customTemplate)) {
            return $this->customTemplate;
        }

        if (!empty($cssClasses)) {
            $divString = " class=\"%cssClass%\"";
            if (!empty($cssIconClasses)) {
                $iconString = "<i class=\"%cssIconClass%\"></i> ";
            }
        }

        return $this->toInterpolate(
            $template,
            [
                'divString'  => $divString,
                'iconString' => $iconString,
            ]
        );
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

        $replaceCss     = $this->checkClasses($this->cssClasses, $type);
        $replaceIconCss = $this->checkClasses($this->cssIconClasses, $type);

        return $this->toInterpolate(
            $this->getTemplate($replaceCss, $replaceIconCss),
            [
                'cssClass'     => $replaceCss,
                'cssIconClass' => $replaceIconCss,
                'message'      => $message,
            ]
        );
    }
}

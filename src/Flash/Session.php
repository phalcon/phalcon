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

use Phalcon\Session\ManagerInterface;

/**
 * This is an implementation of the Phalcon\Flash\FlashInterface that
 * temporarily stores the messages in session, then messages can be printed in
 * the next request.
 *
 * Class Session
 *
 * @package Phalcon\Flash
 */
class Session extends AbstractFlash
{
    private const SESSION_KEY = '_flashMessages';

    /**
     * Clear messages in the session messenger
     *
     * @throws Exception
     */
    public function clear(): void
    {
        $this->getSessionMessages(true);
        parent::clear();
    }

    /**
     * Returns the messages in the session flasher
     *
     * @param string|null $type
     * @param bool        $remove
     *
     * @return array
     * @throws Exception
     */
    public function getMessages(string | null $type = null, bool $remove = true): array
    {
        return $this->getSessionMessages($remove, $type);
    }

    /**
     * Returns the Session Service
     *
     * @return ManagerInterface
     * @throws Exception
     */
    public function getSessionService(): ManagerInterface
    {
        if (null !== $this->sessionService) {
            return $this->sessionService;
        }

        $this->checkContainer(
            Exception::class,
            "the 'session' service"
        );

        if (true !== $this->container->has('session')) {
            $this->checkContainer(
                Exception::class,
                "the 'session' service"
            );
        }

        if (null === $this->sessionService) {
            $this->sessionService = $this->container->getShared('session');
        }


        return $this->sessionService;
    }

    /**
     * Checks whether there are messages
     *
     * @param string|null $type
     *
     * @return bool
     * @throws Exception
     */
    public function has(string | null $type = null): bool
    {
        $messages = $this->getSessionMessages(false);

        if (null === $type) {
            return !empty($messages);
        }

        return isset($messages[$type]);
    }

    /**
     * Adds a message to the session flasher
     *
     * @param string $type
     * @param mixed  $message
     *
     * @return string|null
     * @throws Exception
     */
    public function message(string $type, $message): string | null
    {
        $messages = $this->getSessionMessages(false);

        if (!isset($messages[$type])) {
            $messages[$type] = [];
        }

        $messages[$type][] = $message;

        $this->setSessionMessages($messages);

        return null;
    }

    /**
     * Prints the messages in the session flasher
     *
     * @param bool $remove
     *
     * @throws Exception
     */
    public function output(bool $remove = true): void
    {
        $messages = $this->getSessionMessages($remove);

        foreach ($messages as $type => $message) {
            $this->outputMessage($type, $message);
        }

        parent::clear();
    }

    /**
     * Returns the messages stored in session
     *
     * @param bool        $remove
     * @param string|null $type
     *
     * @return array
     * @throws Exception
     */
    protected function getSessionMessages(bool $remove, string | null $type = null): array
    {
        $session  = $this->getSessionService();
        $messages = $session->get(self::SESSION_KEY);

        /**
         * Session might be empty
         */
        if (!is_array($messages)) {
            $messages = [];
        }

        if (is_string($type)) {
            $return = $messages[$type] ?? [];
            if (true === $remove) {
                unset($messages[$type]);
                $session->set(self::SESSION_KEY, $messages);
            }

            return $return;
        }

        if (true === $remove) {
            $session->remove(self::SESSION_KEY);
        }

        return $messages;
    }

    /**
     * Stores the messages in session
     *
     * @param array $messages
     *
     * @return array
     * @throws Exception
     */
    protected function setSessionMessages(array $messages): array
    {
        $session = $this->getSessionService();

        $session->set(self::SESSION_KEY, $messages);

        return $messages;
    }
}

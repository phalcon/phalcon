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

namespace Phalcon\Db;

use Phalcon\Db\Profiler\Item;
use Phalcon\Db\Traits\ElapsedTimeTrait;

use function method_exists;

/**
 * Instances of Phalcon\Db can generate execution profiles
 * on SQL statements sent to the relational database. Profiled
 * information includes execution time in milliseconds.
 * This helps you to identify bottlenecks in your applications.
 *
 * ```php
 * use Phalcon\Db\Profiler;
 * use Phalcon\Events\Event;
 * use Phalcon\Events\Manager;
 *
 * $profiler = new Profiler();
 * $eventsManager = new Manager();
 *
 * $eventsManager->attach(
 *     "db",
 *     function (Event $event, $connection) use ($profiler) {
 *         if ($event->getType() === "beforeQuery") {
 *             $sql = $connection->getSQLStatement();
 *
 *             // Start a profile with the active connection
 *             $profiler->startProfile($sql);
 *         }
 *
 *         if ($event->getType() === "afterQuery") {
 *             // Stop the active profile
 *             $profiler->stopProfile();
 *         }
 *     }
 * );
 *
 * // Set the event manager on the connection
 * $connection->setEventsManager($eventsManager);
 *
 *
 * $sql = "SELECT buyer_name, quantity, product_name
 * FROM buyers LEFT JOIN products ON
 * buyers.pid=products.id";
 *
 * // Execute a SQL statement
 * $connection->query($sql);
 *
 * // Get the last profile in the profiler
 * $profile = $profiler->getLastProfile();
 *
 * echo "SQL Statement: ", $profile->getSQLStatement(), "\n";
 * echo "Start Time: ", $profile->getInitialTime(), "\n";
 * echo "Final Time: ", $profile->getFinalTime(), "\n";
 * echo "Total Elapsed Time: ", $profile->getTotalElapsedSeconds(), "\n";
 * ```
 */
class Profiler
{
    use ElapsedTimeTrait;

    /**
     * Active Item
     *
     * @var Item|null
     */
    protected ?Item $activeProfile = null;

    /**
     * All the Items in the active profile
     *
     * @var Item[]
     */
    protected array $allProfiles = [];

    /**
     * Maximum number of profiles to retain. 0 (default) keeps the
     * original unbounded behavior; a positive value drops the oldest
     * profile FIFO before a new one is appended.
     *
     * @var int
     */
    protected int $maxProfiles = 0;

    /**
     * Total time spent by all profiles to complete in nanoseconds
     *
     * @var float
     */
    protected float $totalNanoseconds = 0;

    /**
     * Returns the last profile executed in the profiler
     *
     * @return Item|null
     */
    public function getLastProfile(): ?Item
    {
        return $this->activeProfile;
    }

    /**
     * Returns the configured maximum number of retained profiles
     * (0 = unlimited)
     *
     * @return int
     */
    public function getMaxProfiles(): int
    {
        return $this->maxProfiles;
    }

    /**
     * Returns the total number of SQL statements processed
     *
     * @return int
     */
    public function getNumberTotalStatements(): int
    {
        return count($this->allProfiles);
    }

    /**
     * Returns all the processed profiles
     *
     * @return Item[]
     */
    public function getProfiles(): array
    {
        return $this->allProfiles;
    }

    /**
     * Returns the total time in nanoseconds spent by the profiles
     *
     * @return float
     */
    public function getTotalElapsedNanoseconds(): float
    {
        return $this->totalNanoseconds;
    }

    /**
     * Resets the profiler, cleaning up all the profiles
     *
     * @return $this
     */
    public function reset(): static
    {
        $this->allProfiles = [];

        return $this;
    }

    /**
     * Sets the maximum number of retained profiles. 0 disables the cap
     * (the default; preserves the original unbounded behavior).
     *
     * @param int $maxProfiles
     *
     * @return $this
     */
    public function setMaxProfiles(int $maxProfiles): static
    {
        $this->maxProfiles = $maxProfiles;

        return $this;
    }

    /**
     * Starts the profile of a SQL sentence
     *
     * @param string $sqlStatement
     * @param array  $sqlVariables
     * @param array  $sqlBindTypes
     *
     * @return $this
     */
    public function startProfile(
        string $sqlStatement,
        array $sqlVariables = [],
        array $sqlBindTypes = []
    ): static {
        $activeProfile = new Item();

        $activeProfile
            ->setSqlStatement($sqlStatement)
            ->setSqlVariables($sqlVariables)
            ->setSqlBindTypes($sqlBindTypes)
            ->setInitialTime(hrtime(true))
        ;

        if (true === method_exists($this, "beforeStartProfile")) {
            $this->beforeStartProfile($activeProfile);
        }

        $this->activeProfile = $activeProfile;

        return $this;
    }

    /**
     * Stops the active profile
     *
     * @return $this
     */
    public function stopProfile(): static
    {
        $activeProfile = $this->activeProfile;

        $activeProfile->setFinalTime(hrtime(true));

        if ($this->maxProfiles > 0 && count($this->allProfiles) >= $this->maxProfiles) {
            $firstKey = array_key_first($this->allProfiles);
            if (null !== $firstKey) {
                unset($this->allProfiles[$firstKey]);
            }
        }

        $this->totalNanoseconds = $this->totalNanoseconds + $activeProfile->getTotalElapsedNanoseconds();
        $this->allProfiles[]    = $activeProfile;

        if (true === method_exists($this, "afterEndProfile")) {
            $this->afterEndProfile($activeProfile);
        }

        return $this;
    }
}

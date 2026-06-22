# Changelog

## [6.0.0 alpha 3](https://github.com/phalcon/phalcon/releases/tag/v6.0.0alpha3) (2026-xx-xx)


### Changed

- Changed `Phalcon\Support\Debug` into a thin coordinator that delegates exception-data collection to the new `Phalcon\Support\Debug\ReportBuilder` and HTML rendering to a `Phalcon\Contracts\Support\Debug\Renderer` (default `Phalcon\Support\Debug\Renderer\HtmlRenderer`), and exposes `getRenderer()`/`setRenderer()` to swap the renderer. [#17202](https://github.com/phalcon/cphalcon/issues/17202) [[doc]](https://docs.phalcon.io/6.0/support-debug/)
- Changed `Phalcon\Support\Debug` and `Phalcon\Support\Debug\Dump` to render from named, overridable template strings (the new `Phalcon\Contracts\Support\Debug\TemplateAware` contract with `getTemplate()`/`setTemplate()`) filled by the interpolator, instead of inline string concatenation. [#17202](https://github.com/phalcon/cphalcon/issues/17202) [[doc]](https://docs.phalcon.io/6.0/support-debug/)
- Changed the `Phalcon\Support\Debug` exception page to a redesigned, asset-driven layout (masthead with the Phalcon logo, error card, tabbed Request/Server/Included Files/Memory/Variables context, and collapsible backtrace frames); `getCssSources()` and `getJsSources()` now reference a single `debug.css` and `debug.js` instead of the bundled jQuery, jQuery-UI and prettify assets. [#17202](https://github.com/phalcon/cphalcon/issues/17202) [[doc]](https://docs.phalcon.io/6.0/support-debug/)
- Changed `Phalcon\Support\Debug::getVersion()` to return a compact version badge anchor (`v<version>`) instead of the previous "Phalcon Framework" version block. [#17202](https://github.com/phalcon/cphalcon/issues/17202) [[doc]](https://docs.phalcon.io/6.0/support-debug/)
- Changed the `Phalcon\Support\Debug` Memory panel to report both real and peak memory usage. [#17202](https://github.com/phalcon/cphalcon/issues/17202) [[doc]](https://docs.phalcon.io/6.0/support-debug/)

### Added

- Added the `Phalcon\Contracts\Support\Debug\TemplateAware` and `Phalcon\Contracts\Support\Debug\Renderer` contracts, the `Phalcon\Support\Debug\ReportBuilder` and `Phalcon\Support\Debug\Renderer\HtmlRenderer` classes, the `Phalcon\Support\Debug\Traits\TemplateAwareTrait` trait, and the value objects `Phalcon\Support\Debug\Report\ExceptionReport` and `Phalcon\Support\Debug\Report\BacktraceItem`. [#17202](https://github.com/phalcon/cphalcon/issues/17202) [[doc]](https://docs.phalcon.io/6.0/support-debug/)
- Added `Phalcon\Support\Debug::getRenderer()` and `Phalcon\Support\Debug::setRenderer()`. [#17202](https://github.com/phalcon/cphalcon/issues/17202) [[doc]](https://docs.phalcon.io/6.0/support-debug/)
- Added the `Phalcon\Queue` component, a first-class queue/messaging layer modeled on the queue-interop contracts, with the `Phalcon\Contracts\Queue\*` interfaces (`ConnectionFactory`, `Context`, `Destination`, `Queue`, `Topic`, `Producer`, `Consumer`, `SubscriptionConsumer`, `Message`, `Processor`, `VisibilityAware`) and the `Phalcon\Queue\Exceptions\*` hierarchy (`QueueThrowable`, `Exception` and the typed `Invalid*` / `*NotSupportedException` exceptions). [#17051](https://github.com/phalcon/cphalcon/issues/17051) [[doc]](https://docs.phalcon.io/6.0/queue/)
- Added the Memory and Stream queue adapters (`Phalcon\Queue\Adapter\Memory\*`, in-process FIFO; `Phalcon\Queue\Adapter\Stream\*`, file-per-queue with `flock`). [#17051](https://github.com/phalcon/cphalcon/issues/17051) [[doc]](https://docs.phalcon.io/6.0/queue/)
- Added the Redis queue adapter (`Phalcon\Queue\Adapter\Redis\*`) with list-backed FIFO delivery (`LPUSH`/`BRPOP`), sorted-set delivery delay and native blocking receive. [#17051](https://github.com/phalcon/cphalcon/issues/17051) [[doc]](https://docs.phalcon.io/6.0/queue/)
- Added the Beanstalk queue adapter (`Phalcon\Queue\Adapter\Beanstalk\*`) over a dependency-free socket client, with native delivery delay and priority and a `VisibilityAware` consumer (`touch()`). [#17051](https://github.com/phalcon/cphalcon/issues/17051) [[doc]](https://docs.phalcon.io/6.0/queue/)
- Added the `Phalcon\Queue\AdapterFactory` and `Phalcon\Queue\QueueFactory` factories, and registered the `queueFactory` service in `Phalcon\Di\FactoryDefault` and `Phalcon\Di\FactoryDefault\Cli`. [#17051](https://github.com/phalcon/cphalcon/issues/17051) [[doc]](https://docs.phalcon.io/6.0/queue/)
- Added the queue consumer runner (`Phalcon\Queue\Consumer\QueueConsumer`, `Worker`, `WorkerOptions`, `BoundProcessor`, `Events`) and the CLI consumer task `Phalcon\Queue\Cli\ConsumerTask`. [#17051](https://github.com/phalcon/cphalcon/issues/17051) [[doc]](https://docs.phalcon.io/6.0/queue/)
- Added connection-liveness and opt-in auto-reconnect support to `Phalcon\Db\Adapter\Pdo\AbstractPdo`: `ping()` (a `SELECT 1` probe), `ensureConnection()` (reconnect in place when the probe fails), and `setAutoReconnect()`/`getAutoReconnect()` (also settable via the `autoReconnect` descriptor key). When auto-reconnect is enabled and a query fails on a lost ("gone away") connection outside a transaction, `execute()` and `query()` fire the new `db:connectionLost` event, reconnect, and retry the statement once; a loss inside a transaction is re-thrown without retry. "Gone away" detection is provided per driver by `Phalcon\Db\Adapter\Pdo\Mysql` (error codes 2006/2013) and `Phalcon\Db\Adapter\Pdo\Postgresql` (SQLSTATE 08003/08006/57P01-03), with a message fallback. [#17204](https://github.com/phalcon/cphalcon/issues/17204) [[doc]](https://docs.phalcon.io/6.0/db-layer/)
- Added the same liveness and opt-in auto-reconnect support to `Phalcon\DataMapper\Pdo\Connection` (`ping()`, `ensureConnection()`, `setAutoReconnect()`/`getAutoReconnect()`), wrapping `exec()`, `perform()`, `prepare()`, and `query()` with the single-retry behavior. This connection has no events manager, so no `db:connectionLost` event is fired; "gone away" detection is driver-agnostic and the in-transaction guard uses a locally tracked transaction level. [#17204](https://github.com/phalcon/cphalcon/issues/17204) [[doc]](https://docs.phalcon.io/6.0/db-layer/)

### Fixed

- Fixed `Phalcon\Support\Debug` ignoring the `request` entry of `setBlacklist()`: `$_REQUEST` is now filtered against the `request` blacklist, where previously both superglobals were filtered against the `server` blacklist only. [#17202](https://github.com/phalcon/cphalcon/issues/17202) [[doc]](https://docs.phalcon.io/6.0/support-debug/)
- Fixed `Phalcon\Tag\Select::selectField()` to invoke the resultset `using` render callback only when it is a `Closure` (previously any object), keeping the dynamically invoked callable out of reach of user-controlled data. [#17210](https://github.com/phalcon/cphalcon/issues/17210)

### Removed

## [6.0.0 alpha 2](https://github.com/phalcon/phalcon/releases/tag/v6.0.0alpha2) (2026-06-19)
## [6.0.0 alpha 1](https://github.com/phalcon/phalcon/releases/tag/v6.0.0alpha1) (2026-06-19)

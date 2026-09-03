# Changelog

All notable changes are documented here. The format is based on [Keep a Changelog][keep_a_changelog] and this project adheres to [Semantic Versioning][semantic_versioning].

## [6.0.0 beta 11](https://github.com/phalcon/phalcon/releases/tag/v6.0.0beta11) (2026-xx-xx)

### Changed

- ACL role, component and access names can no longer contain `!`, the internal key delimiter; `Phalcon\Acl\Exceptions\ForbiddenDelimiter` is thrown instead.
- Cache file names of `Phalcon\Annotations\Adapter\Stream`, `Phalcon\Mvc\Model\MetaData\Stream` and `Phalcon\Storage\Adapter\Stream` get a hash suffix when the key contains the character that the separator replacement produces (`_` for class names; `/`, `\`, `:` for storage keys), so two different keys can no longer share one file. Names of all other keys are unchanged.
- `Phalcon\Auth\Guard\Session` sets the `Secure` flag of the remember-me cookie from the new `rememberSecure` option (default `true`) instead of the request scheme, so a TLS-terminating proxy that reports plain HTTP to the backend cannot downgrade it.
- `Phalcon\Auth\Guard\Session` validates the "remember me" token against the user agent of the current request instead of the one stored in the cookie; a browser user-agent change now ends a remembered session.
- `Phalcon\Encryption\Security::CRYPT_MD5`, `CRYPT_SHA256` and `CRYPT_SHA512` are documented as weak legacy algorithms to be removed in a future major version; use bcrypt or Argon2 and rehash on login.
- `Phalcon\Storage\Adapter\Stream` creates its shard directories with mode `0755` instead of `0777`. Thanks to [Ilia Alshanetsky](https://ilia.ws)

### Added

- Optional fifth argument `stopOnFalse` on `Phalcon\Events\Manager::fire()` (not on the interface), a per-call override of `setStopOnFalse()`; `EventsAwareTrait::fireManagerEvent()` gained a matching fourth argument.
- `Phalcon\Acl\Exceptions\ForbiddenDelimiter`, thrown when an ACL role, component or access name contains `!`.
- `Phalcon\Auth\Exceptions\InvalidCredentialKey`, thrown when a credential key passed to `Phalcon\Auth\Adapter\Model::retrieveByCredentials()` is not a plain identifier.
- `Phalcon\Http\Request\Bag\AbstractBag::clear()`, removing all elements of a request bag.
- `Phalcon\Mvc\Model::findFirst()` now recognizes the `eager` parameter, so relations can be eagerly loaded. [#17534](https://github.com/phalcon/cphalcon/issues/17534) [[doc]](https://docs.phalcon.io/6.0/db-models/)
- `allowedClasses` option for the Storage adapters (`true`, `false` or a list of class names), forwarded to the new `Phalcon\Storage\Serializer\Php::setAllowedClasses()`: restricts the classes `unserialize()` may instantiate for stored values, including the nested content of the `Stream` adapter. A class outside the list makes the read fail instead of building an object. Thanks to [Ilia Alshanetsky](https://ilia.ws)
- `rememberSecure` option for the session guard (`Phalcon\Auth\Guard\Config\SessionGuardConfig`, `Session::fromOptions()`).

### Fixed

- "Remember me" cookie of another account surviving `Phalcon\Auth\Guard\Session::logout()` when the current user does not implement `AuthRemember`.
- A `false` returned by a listener of `acl:beforeCheckAccess`, `dispatch:beforeDispatch`, `dispatch:beforeExecuteRoute`, `micro:beforeHandleRoute` or `micro:beforeExecuteRoute` being overwritten by a later listener that returned a non-null value; these boundaries now fire with stop-on-false, so a denial is final.
- Asset output following a symbolic link at the target file and writing outside the assets directory.
- Backslash path traversal in `Phalcon\Mvc\View::partial()` and `Phalcon\Mvc\View\Simple::render()` on Windows.
- Cached user surviving `Phalcon\Auth\Guard\Token::setRequest()`, so a replaced request inherited the previous authentication.
- Column comment concatenated unescaped into the `CREATE TABLE` / `ALTER TABLE` DDL of the MySQL dialect; it is now escaped like the DEFAULT clause. Thanks to [Ilia Alshanetsky](https://ilia.ws)
- Credential keys interpolated unvalidated into the PHQL built by `Phalcon\Auth\Adapter\Model::retrieveByCredentials()`.
- Distinct ACL tuples colliding on the same internal key when a role, component or access name contained `!`.
- JWT audience validated with a loose comparison, so a numeric or boolean `aud` claim satisfied a string audience.
- Length-dependent HMAC work on the CBC decrypt failure path of `Phalcon\Encryption\Crypt`, which could still tell a padding failure from a MAC mismatch by timing.
- Malformed ACL snapshot loaded by `Phalcon\Acl\Adapter\Storage` raising `TypeError` or leaving the adapter half loaded, and deep or cyclic object graphs recursing without limit; `InvalidSnapshot` is now thrown before any state changes.
- Namespace middleware bypass in the ADR `Router` through case-variant or separator-injected paths that PHP resolves to the canonical Action class; only the exact declared class name is a match.
- Non-string elements passed to `Phalcon\Acl\Adapter\Memory::addInherit()` raising a warning and a `TypeError` instead of `InvalidRoleType`.
- `Phalcon\Filter\Validation\Validator\File\AbstractFile::checkUpload()` reporting success when the field value is not an uploaded file array; a missing file or a plain string now fails validation. [#17541](https://github.com/phalcon/cphalcon/issues/17541) [[doc]](https://docs.phalcon.io/6.0/filter-validation/)
- `Phalcon\Filter\Validation\Validator\File\Resolution\Equal`, `Max`, `Min` and `AspectRatio` not checking the `false` returned by `getimagesize()`; a file that is not a readable image is now rejected. [#17542](https://github.com/phalcon/cphalcon/issues/17542) [[doc]](https://docs.phalcon.io/6.0/filter-validation/)
- `Phalcon\Filter\Validation\Validator\Ip` ignoring per-field `allowPrivate` and `allowReserved` arrays; the option is now resolved for the field before it becomes a filter flag. [#17548](https://github.com/phalcon/cphalcon/issues/17548) [[doc]](https://docs.phalcon.io/6.0/filter-validation/)
- `Phalcon\Forms\Element\CheckGroup` and `RadioGroup` storing their choices in the property `Phalcon\Forms\Element\AbstractElement` uses for user options, so `setUserOption()` added a choice and `setOptions()` removed every user option. [#17536](https://github.com/phalcon/cphalcon/issues/17536) [[doc]](https://docs.phalcon.io/6.0/forms/)
- `Phalcon\Forms\Element\Select::addOption()` writing with an offset into an object or `null` options value; the write now happens only when the options value is an array (`null` becomes an empty array). [#17536](https://github.com/phalcon/cphalcon/issues/17536) [[doc]](https://docs.phalcon.io/6.0/forms/)
- PHQL `WITH` naming a model instead of a relation alias always throwing `RelationshipNotFound`; the fallback checked `Phalcon\Mvc\Model\Manager::getRelationsBetween()` for an object, but it returns an array. Ambiguous pairs now throw `AmbiguousJoinRelation`. [#17554](https://github.com/phalcon/cphalcon/issues/17554) [[doc]](https://docs.phalcon.io/6.0/db-models/)
- `Phalcon\Mvc\Model\Manager` relation guards throwing `TypeError` on a string field list, and the second check in `addHasManyToMany()` / `addHasOneThrough()` comparing the same pair twice. [#17556](https://github.com/phalcon/cphalcon/issues/17556) [[doc]](https://docs.phalcon.io/6.0/db-models/)
- Request attributes of the previous route surviving on a reused request in `Phalcon\ADR\Application::handle()`.
- Scheme allow-list bypass in the Filter `url` sanitizer through HTML-entity obfuscated schemes (`java&#115;cript:`) and URLs that `parse_url()` cannot parse; the sanitizer now fails closed. Thanks to [Ilia Alshanetsky](https://ilia.ws)
- Validators `Alpha`, `Alnum`, `Confirmation`, `CreditCard`, `Digit`, `Numericality`, `Regex`, `StringLength\Min` and `StringLength\Max` cast an array value to the constant `"Array"`, so `field[]=x` passed alphabetic, alphanumeric, length and confirmation checks; a value that cannot be a string is now rejected with the validator's message. Thanks to [Ilia Alshanetsky](https://ilia.ws)
- View parameters named `path` or `compiledTemplatePath` replacing the file included by the `Php` and `Volt` view engines.
- Volt extends-mode cache unserialized without a class restriction.
- `Phalcon\Tag::getEscaper()` treated every value other than `true` as escaping disabled, so `1` or `"1"` silently switched escaping off; it now matches cphalcon (only a falsy value disables it).
- `ReflectionException` / `TypeError` from ACL rule callbacks with builtin-typed parameters, array callables or static-method strings.
- `acl:afterCheckAccess` reporting the static rule instead of the final `isAllowed()` decision (rule callback and default action were not applied).
- `only()` / `except()` action filters leaking between `Phalcon\Auth\Manager::access()` activations when the access gate was registered as a shared service in the legacy `Di`.

### Removed


## [6.0.0 beta 10](https://github.com/phalcon/phalcon/releases/tag/v6.0.0beta10) (2026-08-25)

### Changed

- The PHPUnit configuration now fails the run on notices, deprecations and PHPUnit deprecations, and prints the details of every triggering test.
- Added regression tests for the security hardening changes that had no failing-on-revert coverage.

### Added

### Fixed

- `Phalcon\Db\Dialect\Mysql::getColumnDefinition()` appended `VARCHAR` to a custom string type and ignored `typeValues`, so ENUM/SET columns rendered as `ENUMVARCHAR` instead of `ENUM('a', 'b')`; the method now mirrors cphalcon, including the escaping of the values.
- Stale shadow flag clean-up in `Phalcon\Mvc\Router` now checks the entry exists before removing it, in sync with cphalcon [#17527](https://github.com/phalcon/cphalcon/issues/17527).

### Removed


## [6.0.0 beta 9](https://github.com/phalcon/phalcon/releases/tag/v6.0.0beta9) (2026-08-24)

### Changed

### Added

### Fixed

- Attribute name and empty-option injection in the legacy Tag / Forms Select element.
- Attribute name and tag name injection in the Html Tag helpers.
- Attribute name splitting in the Html Escaper `attributes()` array keys.
- Backslash breakout in the Html Escaper `js()` output.
- Broken string-literal escaping of column defaults and ENUM/SET values in the Db dialects.
- CBC padding-oracle exposure in `Crypt::decrypt()`, where a padding failure was distinguishable from a MAC mismatch.
- Command injection via CR/LF in Beanstalk queue tube names.
- CORS credentials paired with a reflected wildcard origin in the ADR Cors middleware.
- Dangerous URL scheme (`javascript:`, `data:`) passthrough in the Filter `url` sanitizer.
- Format-string denial of service in the Translate indexed-array interpolator.
- Log injection via unescaped control characters in the Logger line formatter.
- Missing Secure and HttpOnly flags on the Auth "remember me" cookie.
- Non-constant-time credential comparison in the Auth memory and stream adapters.
- Non-cryptographic randomness in the ISO-10126 padding scheme.
- Object injection through unrestricted `unserialize()` in the Storage Stream payload read.
- Only the first configured trusted proxy checked when resolving the Request client address.
- Open redirect in the ADR redirect responder bypassing the Http Response `redirect()` gate.
- Open redirect via absolute and protocol-relative targets in the Http Response `redirect()`.
- Path traversal in the View `partial()` and Simple `render()` paths.
- Path traversal via the cache key in the Storage Stream adapter.
- Path traversal via the session id in the Session Stream adapter.
- Reflected XSS via unescaped array keys in the Debug dump and exception renderer.
- SQL injection in the Db Dialect schema-introspection methods.
- SQL injection via uncast LIMIT and OFFSET values in the Db Dialect `limit()`.
- Session fixation in the Auth Session guard login.
- Session id not regenerated on the Auth Session guard logout.
- Unbounded image dimensions (decompression bomb / pixel flood) in the GD and Imagick image adapters.
- Unbounded recursion in the WKB geometry parser.
- Unescaped CSS class in Flash messages.
- Unescaped identifier quoting in the DataMapper PDO connection.
- Unescaped label and link in the deprecated Html Breadcrumbs.
- Unescaped label text in the Html checkbox and radio helpers.
- Unescaped link in the Html breadcrumbs helper.
- Wrong route dispatched by the Router fast path when a static route follows a shadowing regex.

### Removed

## [6.0.0 beta 8](https://github.com/phalcon/phalcon/releases/tag/v6.0.0beta8) (2026-08-22)

### Changed

### Added

### Fixed

- Single-quoted string emission in Volt Compiler.
- Quoted parameters in PHQL/Query

### Removed

## [6.0.0 beta 7](https://github.com/phalcon/phalcon/releases/tag/v6.0.0beta7) (2026-08-19)

### Changed

- Changed `Phalcon\Filter\Validation\Validator\Callback` to stop binding the callback closure to the validator; to set the message from inside the callback ([#17255](https://github.com/phalcon/cphalcon/issues/17255)), declare a second parameter and call `$validator->setTemplate()` instead of `$this->setTemplate()` [#17499](https://github.com/phalcon/cphalcon/issues/17499) [[doc]](https://docs.phalcon.io/6.0/filter-validation/)
- Changed `Phalcon\Filter\Validation\Validator\StringLength`, `Phalcon\Filter\Validation\Validator\StringLength\Min` and `Phalcon\Filter\Validation\Validator\StringLength\Max` to treat `min` and `max` as inclusive when the `included` option is not set, matching the class documentation and the behavior before 5.7.0; set `included` to `false` for exclusive boundaries [#17503](https://github.com/phalcon/cphalcon/issues/17503) [[doc]](https://docs.phalcon.io/6.0/filter-validation/)

### Added

- Added `includedMinimum` and `includedMaximum` as aliases of the `included` option in `Phalcon\Filter\Validation\Validator\StringLength\Min` and `Phalcon\Filter\Validation\Validator\StringLength\Max`, so the option names of the `StringLength` container also work on the two validators directly. `included` has precedence if you set both [#17503](https://github.com/phalcon/cphalcon/issues/17503) [[doc]](https://docs.phalcon.io/6.0/filter-validation/)
- Added lifecycle events to the DataMapper connections, fired through `Phalcon\Events\Manager` and named on the new `Phalcon\DataMapper\Pdo\Events` class: `dm:beforeConnect`/`dm:afterConnect`, `dm:beforeDisconnect`/`dm:afterDisconnect`, `dm:beforePerform`/`dm:afterPerform`, `dm:beforeExec`/`dm:afterExec`, `dm:beforeQuery`/`dm:afterQuery`, `dm:beforeBeginTransaction`/`dm:afterBeginTransaction`, `dm:beforeCommit`/`dm:afterCommit`, `dm:beforeRollBack`/`dm:afterRollBack` and `dm:connectionLost`. `prepare()` has no events of its own because `perform()` calls it, which would report one operation twice. The connect, disconnect and connectionLost events report a change of the connection state and fire whichever method causes it, so an automatic reconnect reports the lost connection and the new one. `Phalcon\DataMapper\Pdo\Connection\Decorated` does not fire the connect and disconnect events because it never connects and cannot disconnect. [#17501](https://github.com/phalcon/cphalcon/issues/17501) [[doc]](https://docs.phalcon.io/6.0/datamapper/)
- Added `getEventsManager()` and `setEventsManager()` to `Phalcon\DataMapper\Pdo\Connection\AbstractConnection`, and therefore to `Phalcon\DataMapper\Pdo\Connection` and `Phalcon\DataMapper\Pdo\Connection\Decorated`, and to `Phalcon\DataMapper\Pdo\ConnectionLocator`, which gives its events manager to every connection it returns, including the ones it builds on demand. The two classes now carry the `Phalcon\Contracts\Events\EventsAware` contract. `Phalcon\DataMapper\Pdo\Connection\ConnectionInterface` is unchanged, so classes that implement it directly keep working [#17501](https://github.com/phalcon/cphalcon/issues/17501) [[doc]](https://docs.phalcon.io/6.0/datamapper/)
- Added `Phalcon\DataMapper\Pdo\Exception\OperationCancelled`, thrown when a listener cancels one of the `before*` events. The operation does not run. To cancel, a listener must call `$event->stop()` and also return `false`: `stop()` alone returns the value of the listener, which the connection cannot tell apart from "no listeners", and `false` alone is replaced by any later listener that returns a value while the events manager is not in `stopOnFalse` mode. The `after*` events are not cancellable [#17501](https://github.com/phalcon/cphalcon/issues/17501) [[doc]](https://docs.phalcon.io/6.0/datamapper/)

### Fixed

- Fixed `Phalcon\Filter\Validation\Validator\Callback` rebinding `$this` in callback closures; the validator is now passed as a second argument to closures that declare one [#17499](https://github.com/phalcon/cphalcon/issues/17499) [[doc]](https://docs.phalcon.io/6.0/filter-validation/)
- Fixed `Phalcon\Filter\Validation\Validator\StringLength\Min` and `Phalcon\Filter\Validation\Validator\StringLength\Max` rejecting a string with a length exactly equal to `min` or `max` when the `included` option was not set, a regression introduced in 5.7.0 [#17503](https://github.com/phalcon/cphalcon/issues/17503) [[doc]](https://docs.phalcon.io/6.0/filter-validation/)
- Fixed `Phalcon\Filter\Validation\Validator\StringLength` giving the `includedMinimum`/`includedMaximum` and `messageMinimum`/`messageMaximum` option of one boundary to the validator of the other boundary [#17503](https://github.com/phalcon/cphalcon/issues/17503) [[doc]](https://docs.phalcon.io/6.0/filter-validation/)
- Fixed `Phalcon\Html\Helper\Input\Generic`, `Phalcon\Html\Helper\Input\Checkbox` and `Phalcon\Html\Helper\Input\Radio` throwing an error when you build them directly without a `Phalcon\Html\Helper\Doctype`, which their constructor accepts as optional. `Phalcon\Html\Helper\Input\AbstractInput::__toString()` now renders HTML5 when there is no doctype, the same as `Phalcon\Html\Helper\VoidTag` and the same as a default `Doctype`. Helpers that come from `Phalcon\Html\TagFactory` always get a doctype, so their output does not change [#17507](https://github.com/phalcon/cphalcon/issues/17507) [[doc]](https://docs.phalcon.io/6.0/html-tagfactory/)
- Fixed `Phalcon\Image\Adapter\Imagick::background()`, `reflection()`, `text()` and `watermark()` discarding any opacity below 100, and `sharpen()` rounding its amount down, because the division by 100 was stored back in the integer parameter; `watermark()` left the image unchanged for every opacity except 100 [#17510](https://github.com/phalcon/cphalcon/issues/17510) [[doc]](https://docs.phalcon.io/6.0/image/)
- Fixed `Phalcon\Image\Adapter\Imagick` never coalescing the frames of a GIF, because the constructor compared `getImageType()`, an Imagick `IMGTYPE_*` value, against `IMAGETYPE_GIF`; the width and the height of an animated GIF now describe the canvas instead of whichever sub frame the cursor was on [#17510](https://github.com/phalcon/cphalcon/issues/17510) [[doc]](https://docs.phalcon.io/6.0/image/)
- Fixed `Phalcon\Image\Adapter\Imagick::render()` returning the current frame alone for a GIF, and `save()` failing with `no encode delegate for this image format` after an operation that rebuilds the image; both now mark every frame with the format, which `setImageFormat()` applies to the current frame only [#17510](https://github.com/phalcon/cphalcon/issues/17510) [[doc]](https://docs.phalcon.io/6.0/image/)

### Removed

## [6.0.0 beta 6](https://github.com/phalcon/phalcon/releases/tag/v6.0.0beta6) (2026-08-02)

### Changed

### Added

### Fixed

- Fixed `Phalcon\Mvc\View::partial()` and `render()` throwing `ViewNotFound` for absolute paths [#17426](https://github.com/phalcon/cphalcon/issues/17426)

### Removed

## [6.0.0 beta 5](https://github.com/phalcon/phalcon/releases/tag/v6.0.0beta5) (2026-07-31)

### Changed

- Changed `Phalcon\Mvc\Model\Resultset::refresh()` to reset the cursor - position, current row, buffered rows and active row - after replaying the statement. [#17399](https://github.com/phalcon/cphalcon/issues/17399) [[doc]](https://docs.phalcon.io/6.0/db-models/)
- Changed `Phalcon\Events\Manager::getEventTypes()` to `getListenerMap()`, which now returns each event type mapped to its listeners. [#17416](https://github.com/phalcon/cphalcon/issues/17416) [[doc]](https://docs.phalcon.io/6.0/events/)

### Added

- Added `Phalcon\ADR\Front\AbstractHttpFront::boot()`, which builds the container, loads the environment and registers the providers, then returns the container - for consumers that need it before, or instead of, `run()`. The container is built once and cached, so `boot()` and `run()` share the same instance. A bootstrap file can now be `return (new AppFront(dirname(__DIR__)))->boot();`. [#17413](https://github.com/phalcon/cphalcon/issues/17413) [[doc]](https://docs.phalcon.io/6.0/adr/)
- Added `Phalcon\Contracts\Container\Service\Enumerable` implemented in `Phalcon\Container\Container`. [#17416](https://github.com/phalcon/cphalcon/issues/17416) [[doc]](https://docs.phalcon.io/6.0/container/)
- Added `Phalcon\Contracts\Events\Enumerable`, implemented in `Phalcon\Events\Manager`. [#17416](https://github.com/phalcon/cphalcon/issues/17416) [[doc]](https://docs.phalcon.io/6.0/events/)
- Added `methodFor()` to the `Phalcon\Contracts\ADR\Router\Router` contract and to `Phalcon\ADR\Router\Router`. It names the HTTP method an Action class answers, the counterpart to `pathFor()`. [#17416](https://github.com/phalcon/cphalcon/issues/17416) [[doc]](https://docs.phalcon.io/6.0/adr/)

### Fixed

- Fixed `Phalcon\Mvc\Model\Resultset` costing two statements for every resultset on SQLite. [#17399](https://github.com/phalcon/cphalcon/issues/17399) [[doc]](https://docs.phalcon.io/6.0/db-models/)
- Fixed `Phalcon\Db\Result\PdoResult::numRows()` failing on multi-line statements. [#17399](https://github.com/phalcon/cphalcon/issues/17399)
- Fixed `Phalcon\Mvc\Model\Resultset::seek()` leaving the previous row in place as the current one when seeking past the end of a resultset held in memory. [#17399](https://github.com/phalcon/cphalcon/issues/17399) [[doc]](https://docs.phalcon.io/6.0/db-models/)
- Fixed `Phalcon\Db\Adapter\Pdo\Mysql::describeColumns()` returning string, date and time defaults still wrapped in quotes on MariaDB, e.g. `'fi'` instead of `fi`. MariaDB reports `COLUMN_DEFAULT` as the DDL source rather than the resolved literal. Expression defaults are unaffected. Note that on MariaDB this changes what `Phalcon\Db\Column::getDefault()` returns and what the model layer writes into an unset `NOT NULL` column on save; if you were stripping the quotes yourself, remove that workaround. MySQL is unaffected. [#17417](https://github.com/phalcon/cphalcon/issues/17417) [[doc]](https://docs.phalcon.io/6.0/db-layer/)
- Fixed `Phalcon\Db\Adapter\Pdo\Mysql::describeColumns()` reporting a column declared `DEFAULT NULL` as the literal string `"NULL"` on MariaDB, which was then written back onto the model attribute on save. [#17176](https://github.com/phalcon/cphalcon/issues/17176) [[doc]](https://docs.phalcon.io/6.0/db-layer/)

### Removed

## [6.0.0 beta 4](https://github.com/phalcon/phalcon/releases/tag/v6.0.0beta4) (2026-07-28)

### Changed

- Changed `Phalcon\ADR\Router\Router` to derive exactly one Action class per path: the class name is the verb followed by every static segment concatenated. [#17410](https://github.com/phalcon/cphalcon/issues/17410) [[doc]](https://docs.phalcon.io/6.0/adr/)

### Added

- Added `classFor()` to the `Phalcon\Contracts\ADR\Router\Router` contract and to `Phalcon\ADR\Router\Router`. It names the Action class the convention would use for a static path. [#17410](https://github.com/phalcon/cphalcon/issues/17410) [[doc]](https://docs.phalcon.io/6.0/adr/)

### Fixed

- Fixed `Phalcon\Mvc\Model::find()` and `Phalcon\Mvc\Model::findFirst()` raising an error `Call to undefined method static::getpreparedquery()`. Using `self` vs. 'static` for the internal calls. [#17409](https://github.com/phalcon/cphalcon/issues/17409) [[doc]](https://docs.phalcon.io/6.0/db-models/)

### Removed

## [6.0.0 beta 3](https://github.com/phalcon/phalcon/releases/tag/v6.0.0beta3) (2026-07-27)

### Changed

- Changed `Phalcon\ADR\Router\Router` from a candidate chain to a namespace descent: a path segment becomes a namespace segment only if the matching directory exists, after which at most two Action classes are probed instead of five. An Action can no longer be silently shadowed by an earlier candidate. Requires `setActionDirectory()`. [#17405](https://github.com/phalcon/cphalcon/issues/17405) [[doc]](https://docs.phalcon.io/6.0/adr/)

### Added

- Added `pathFor()`, `setActionDirectory()` and `setWordSeparator()` to the `Phalcon\Contracts\ADR\Router\Router` contract and to `Phalcon\ADR\Router\Router`; the two setters are also on `Phalcon\ADR\Application`. `pathFor()` is the inverse of the routing convention, returning the canonical path an Action answers or `null`. Added `Phalcon\ADR\Exceptions\ActionDirectoryNotSet`. [#17405](https://github.com/phalcon/cphalcon/issues/17405) [[doc]](https://docs.phalcon.io/6.0/adr/)
- Added `Phalcon\Container\Container::getServiceNames()`, returning the names of every registered service definition. Names that only exist as an alias, a pre-set instance or a parameter are not included. [#17406](https://github.com/phalcon/cphalcon/issues/17406) [[doc]](https://docs.phalcon.io/6.0/container/)
- ~~Added `Phalcon\Events\Manager::getEventTypes()`, returning the event types that currently have at least one listener attached, including those contributed by subscribers. Listeners attached with `attach()` were previously not enumerable at all, since `getListeners()` requires a known event type. [#17406](https://github.com/phalcon/cphalcon/issues/17406) [[doc]](https://docs.phalcon.io/6.0/events/)~~

### Fixed

- Fixed `Phalcon\ADR\Router\Router` treating `-` and `_` as the same delimiter, so `/forgot-password` and `/forgot_password` resolved to the same Action and the path-to-class map had no inverse. A single separator now applies in both directions, default `-` and settable with `setWordSeparator()`; `_` is literal. [#17405](https://github.com/phalcon/cphalcon/issues/17405) [[doc]](https://docs.phalcon.io/6.0/adr/)

### Removed

## [6.0.0 beta 2](https://github.com/phalcon/phalcon/releases/tag/v6.0.0beta1) (2026-07-26)

### Changed

- Changed `Phalcon\Mvc\Model::getRelated()` and `Phalcon\Mvc\Model::isRelationshipLoaded()` to test the relation cache with `array_key_exists()` instead of `isset()`, so a to-one relation that resolves to no record is no longer re-queried on every access [#17331](https://github.com/phalcon/cphalcon/issues/17331) [[doc]](https://docs.phalcon.io/6.0/db-models-relationships/)
- Changed `Phalcon\Mvc\Model\Manager::mergeFindParameters()` from `final protected` to `final public static` [#17331](https://github.com/phalcon/cphalcon/issues/17331)

### Added

- Added `Phalcon\ADR\Responder\ViewResponder`, which renders a `.phtml` template and returns it as an HTML response. The action picks the template with `withTemplate()`, and the view receives `result`, `messages` and `status`. Any renderer implementing the new `Phalcon\Contracts\View\Renderer` can be used - `Phalcon\Mvc\View\Simple` now does. [#17379](https://github.com/phalcon/cphalcon/issues/17379) [[doc]](https://docs.phalcon.io/6.0/adr/)
- Added opt-in route-parameter pre-filtering to the ADR convention router via the new `Phalcon\ADR\Router\AttributeFilter`. An Action that declares a static `params()` method has its positional route segments validated against a regex, cast to a scalar type (`int`, `float`, `string`) and optionally passed through a converter closure, then written to the request as named attributes - all before the Action runs. A regex miss is treated as a route miss (404). [#17393](https://github.com/phalcon/cphalcon/issues/17393) [[doc]](https://docs.phalcon.io/6.0/adr/)
- Added eager loading of model relations: `Phalcon\Mvc\Model::find()` accepts an `eager` parameter - an array of dot-delimited relation paths, optionally `path => options` - which pre-loads the named relations with one query per relation instead of one per record. `Phalcon\Mvc\Model\Criteria::eager()` exposes the same on the criteria surface. [#17331](https://github.com/phalcon/cphalcon/issues/17331) [[doc]](https://docs.phalcon.io/6.0/db-models-relationships/)
- Added `candidatesFor()` to the `Phalcon\Contracts\ADR\Router\Router` contract and to `Phalcon\ADR\Router\Router`. It returns every Action class the convention router would try for a given HTTP method and path, in the order it tries them. [#17403](https://github.com/phalcon/cphalcon/issues/17403) [[doc]](https://docs.phalcon.io/6.0/adr/)

### Fixed

- Fixed `Phalcon\Http\Response\Cookies::delete()` not deleting a cookie that was not set in the same request; it now falls back to the `$_COOKIE` superglobal. `Phalcon\Http\Cookie::delete()` expires the cookie with its stored `path` and `domain` instead of the defaults. [#17395](https://github.com/phalcon/cphalcon/issues/17395) [[doc]](https://docs.phalcon.io/6.0/http-response/)

### Removed

## [6.0.0 beta 1](https://github.com/phalcon/phalcon/releases/tag/v6.0.0beta1) (2026-07-24)

### Changed

- Changed `Phalcon\Mvc\Model::getRelated()` and `Phalcon\Mvc\Model::isRelationshipLoaded()` to test the relation cache with `array_key_exists()` instead of `isset()`, so a to-one relation that resolves to no record is no longer re-queried on every access [#17331](https://github.com/phalcon/cphalcon/issues/17331) [[doc]](https://docs.phalcon.io/6.0/db-models-relationships/)
- Changed `Phalcon\Mvc\Model\Manager::mergeFindParameters()` from `final protected` to `final public static` [#17331](https://github.com/phalcon/cphalcon/issues/17331)

### Added

- Added `Phalcon\ADR\Responder\ViewResponder`, which renders a `.phtml` template and returns it as an HTML response. The action picks the template with `withTemplate()`, and the view receives `result`, `messages` and `status`. Any renderer implementing the new `Phalcon\Contracts\View\Renderer` can be used - `Phalcon\Mvc\View\Simple` now does. [#17379](https://github.com/phalcon/cphalcon/issues/17379) [[doc]](https://docs.phalcon.io/6.0/adr/)
- Added opt-in route-parameter pre-filtering to the ADR convention router via the new `Phalcon\ADR\Router\AttributeFilter`. An Action that declares a static `params()` method has its positional route segments validated against a regex, cast to a scalar type (`int`, `float`, `string`) and optionally passed through a converter closure, then written to the request as named attributes - all before the Action runs. A regex miss is treated as a route miss (404). [#17393](https://github.com/phalcon/cphalcon/issues/17393) [[doc]](https://docs.phalcon.io/6.0/adr/)
- Added eager loading of model relations: `Phalcon\Mvc\Model::find()` accepts an `eager` parameter - an array of dot-delimited relation paths, optionally `path => options` - which pre-loads the named relations with one query per relation instead of one per record. `Phalcon\Mvc\Model\Criteria::eager()` exposes the same on the criteria surface. [#17331](https://github.com/phalcon/cphalcon/issues/17331) [[doc]](https://docs.phalcon.io/6.0/db-models-relationships/)

### Fixed

- Fixed `Phalcon\Mvc\Model::assign()` skipping any value that is `null`, because the incoming data was tested with `isset()`. A `null` is a value the caller asked to assign, so `refresh()` could not restore a column to `NULL` and `assign(["column" => null])` was a no-op; the check now uses `array_key_exists()`. [#17331](https://github.com/phalcon/cphalcon/issues/17331) [[doc]](https://docs.phalcon.io/6.0/db-models/)
- Fixed `Phalcon\Container\Definition\ServiceDefinition::resolveArgs()` treating any constructor-argument object that merely exposes a `resolve()` method as a lazy value, using `method_exists()`. Because `Phalcon\Container\Container` defines a private `resolve()`, autowiring a service whose constructor receives the container - for example `__construct(?Container $container)`, as `Phalcon\ADR\Application` does - mistook the injected container for a lazy resolvable and called its private `resolve()`, raising `Error: Call to private method Phalcon\Container\Container::resolve() from scope Phalcon\Container\Definition\ServiceDefinition`. The lazy check now tests `instanceof Phalcon\Contracts\Container\Resolver\Resolvable`. [#17391](https://github.com/phalcon/cphalcon/issues/17391)

### Removed

## [6.0.0 alpha 6](https://github.com/phalcon/phalcon/releases/tag/v6.0.0alpha5) (2026-07-22)

### Changed

- Changed `Phalcon\ADR\Application` into a self-contained composition root: it owns (or accepts) a `Phalcon\Container\Container` and exposes a small registration surface - `bind()`, `define()`, `factory()`, `set()`, `extend()` and `getContainer()` - plus `setBaseNamespace()` and `secureWith()` for convention-router and namespace-prefix guard configuration. `Phalcon\ADR\Front\AbstractHttpFront` gained a protected `getApplication()` hook returning `Phalcon\Contracts\ADR\Application`, so a front controller can configure the application or wire a different implementation. [#17389](https://github.com/phalcon/cphalcon/issues/17389) [[doc]](https://docs.phalcon.io/6.0/adr/)

### Added

### Fixed

### Removed

## [6.0.0 alpha 5](https://github.com/phalcon/phalcon/releases/tag/v6.0.0alpha5) (2026-07-21)

### Added

- Added the Action-Domain-Responder (ADR) HTTP stack under `Phalcon\ADR`, an alternative to MVC that splits request handling into three focused roles: an _Action_ (one invokable class per route) drives a _Domain_ (your business logic, which returns a `Phalcon\ADR\Payload\Payload` and never touches HTTP) and hands the result to a _Responder_ that turns it into a response. `Phalcon\ADR\Application::handle()` routes the request with the convention-based `Phalcon\ADR\Router\Router` - the HTTP method, resource and optional operation resolve the Action class (e.g. `GET /invoices/list` maps to `MyApp\Action\Invoices\GetInvoicesList`), with no route table - writes the matched positional attributes onto the request, and runs the Action through `Phalcon\ADR\Dispatcher`, which resolves it from the container and wraps it in a middleware pipeline (`Phalcon\ADR\Middleware\CorsMiddleware`, `MethodOverrideMiddleware`, `RequestIdMiddleware` and `TimingMiddleware` ship built-in). The Action reads request data through the extendable `Phalcon\ADR\Input\Input` bag and returns a response built by a responder - `Phalcon\ADR\Responder\JsonResponder`, `TextResponder`, `RedirectResponder`, `StatusResponder` and the composable `ChainResponder`/`FormatResponder` - each mapping the payload's `Phalcon\ADR\Payload\Status` to an HTTP status via `Phalcon\ADR\Responder\StatusMapper`. Any escaping `Throwable` is turned into a response by the always-wired `Phalcon\ADR\ErrorResponder`, and `Phalcon\ADR\Emitter\SapiEmitter` sends it to the client. `Phalcon\ADR\Front\HttpFront` - a `Phalcon\Contracts\Front\FrontController` following the front-interop contract - boots the whole stack with a single `run()`, and `Phalcon\ADR\Container\AdrProvider` registers every service. Because route attributes are positional, `Phalcon\Http\Request\Bag\AbstractBag` now accepts integer keys alongside string keys. Contracts live under `Phalcon\Contracts\ADR`. [#17341](https://github.com/phalcon/cphalcon/issues/17341) [[doc]](https://docs.phalcon.io/6.0/adr/)
- Added request attributes support to `Phalcon\Http\Request`. `Phalcon\Http\Request::getAttributes()` returns a `Phalcon\Http\Request\Bag\AttributeBag`, a mutable, string-keyed bag of arbitrary application-defined values attached to the request during its lifecycle (router, dispatcher, security components etc.). Writing with a `null` key (the `$bag[] = ...` append form) throws the new `Phalcon\Http\Request\Exceptions\NullKeyException`, since bag elements are always string-keyed. [#17367](https://github.com/phalcon/cphalcon/issues/17367) [[doc]](https://docs.phalcon.io/6.0/http-request/)

### Fixed

- Fixed `Phalcon\Mvc\Model` ignoring attributes registered with `skipAttributes()`, `skipAttributesOnCreate()` and `skipAttributesOnUpdate()`, so a skipped column was emitted in the generated `INSERT`/`UPDATE` (breaking, for instance, inserts into a table with a MySQL generated column). The skip list is keyed with `null` values, which `isset()` reports as absent, so every skipped attribute read as not registered; the checks in `doLowInsert()`, `doLowUpdate()` and the not-null validation now use `array_key_exists()`. [#17382](https://github.com/phalcon/cphalcon/issues/17382) [[doc]](https://docs.phalcon.io/6.0/db-models/)
- Fixed `Phalcon\Mvc\Model` inserting a literal `null` for a column the database can supply a value for, instead of the `DEFAULT` keyword (or omitting the column on an adapter without `DEFAULT` support, such as SQLite). A nullable column carrying no explicit default is registered in the metadata default values with a `null` value, which the `isset()` check in `doLowInsert()` read as absent; it now uses `array_key_exists()`. This makes inserts work against a MySQL `GENERATED ALWAYS AS (...) STORED` column, which rejects an explicit `null` with `SQLSTATE[HY000]: General error: 3105` but accepts `DEFAULT`. [#17382](https://github.com/phalcon/cphalcon/issues/17382) [[doc]](https://docs.phalcon.io/6.0/db-models/)

## [6.0.0 alpha 4](https://github.com/phalcon/phalcon/releases/tag/v6.0.0alpha1) (2026-07-13)

### Changed

- Converted the internal test suite to the `phalcon/talon` testing framework and added octocov coverage reporting. [#769](https://github.com/phalcon/phalcon/issues/769)

### Added

- Added `Phalcon\Encryption\Security\JWT\Validator::validateSubject()`, which compares the token's `sub` claim against the expected subject and reports `Validation: incorrect subject` on a mismatch. A `null` subject expresses no expectation and is skipped. [#17361](https://github.com/phalcon/cphalcon/issues/17361) [[doc]](https://docs.phalcon.io/6.0/encryption-security-jwt/)
- Added `Phalcon\Filter\Validation::setDefaultMessages()` and `Phalcon\Filter\Validation::getDefaultMessage()` for registering global default validator failure messages keyed by validator class name (e.g. `Validation::setDefaultMessages([PresenceOf::class => 'Default message :field is required'])`). A registered default overrides a validator's built-in class default message, while a message set on the validator instance (the constructor `message`/`template` option or `setTemplate()`) still takes precedence; it applies to validators whose message is produced through `getTemplate()`/`messageFactory()`. [#17257](https://github.com/phalcon/cphalcon/issues/17257) [[doc]](https://docs.phalcon.io/6.0/filter-validation/)
- Added `Phalcon\Mvc\Model\Query::setResultsetRowClass()` and `Phalcon\Mvc\Model\Query::getResultsetRowClass()` to control the class used to hydrate rows that are not mapped to a model (custom-column `SELECT`s and joins). When set, those result rows are built as the given subclass of `Phalcon\Mvc\Model\Row` instead of `Row` itself, on both the simple and complex resultset paths. The class must exist and be a subclass of `Phalcon\Mvc\Model\Row`, otherwise `Phalcon\Mvc\Model\Query\Exceptions\ResultsetRowClassNotFound` or `Phalcon\Mvc\Model\Query\Exceptions\InvalidResultsetRowClass` is thrown. [#17337](https://github.com/phalcon/cphalcon/issues/17337) [[doc]](https://docs.phalcon.io/6.0/db-models/)
- Added `Phalcon\Mvc\Model\Query\Builder::setResultsetRowClass()` and `Phalcon\Mvc\Model\Query\Builder::getResultsetRowClass()` so the custom resultset row class can be set on a query builder, which forwards it to the `Phalcon\Mvc\Model\Query` it produces in `getQuery()`. Because `Phalcon\Paginator\Adapter\QueryBuilder` builds its query through the builder, `paginate()` now returns the given `Phalcon\Mvc\Model\Row` subclass for its non-model result rows. [#17337](https://github.com/phalcon/cphalcon/issues/17337) [[doc]](https://docs.phalcon.io/6.0/db-models/)
- Added an opt-in "sticky" read/write connection mode to `Phalcon\Mvc\Model\Manager`. After `Phalcon\Mvc\Model\Manager::setSticky(true)`, once a model has written to its write connection during the current request cycle, any further reads for that write service are served from the write connection, so data written earlier in the request can be read back immediately. Writes are recorded via the new `Phalcon\Mvc\Model\Manager::registerWrite()` (called internally on a successful insert/update/delete), and `Phalcon\Mvc\Model\Manager::resetConnectionState()` clears the per-request tracking for long-running runtimes (e.g. Swoole, RoadRunner) that reuse the manager across requests. Sticky is off by default, preserving the existing read/write split; the transaction connection still takes precedence. The three methods are added to `Phalcon\Mvc\Model\ManagerInterface`. [#17256](https://github.com/phalcon/cphalcon/issues/17256) [[doc]](https://docs.phalcon.io/6.0/db-models/)

### Fixed

- Fixed the model `Annotations` metadata strategy (`Phalcon\Mvc\Model\MetaData\Strategy\Annotations`) to read the `#[Column]` skip and empty-string flags using the attribute constructor's camelCase argument names (`skipOnInsert`, `skipOnUpdate`, `allowEmptyString`); it previously looked for snake_case keys (`skip_on_insert`, `skip_on_update`, `allow_empty_string`) that never match a valid `#[Column]` usage, so those flags had no effect. [[doc]](https://docs.phalcon.io/6.0/annotations/)
- Fixed `Phalcon\Annotations\Router\Route` (and its `#[Get]`, `#[Post]`, ... subclasses) to declare the `beforeMatch` constructor argument that `Phalcon\Mvc\Router\Annotations` already applies; instantiating the attribute with `beforeMatch:` previously raised an unknown-named-parameter error. [[doc]](https://docs.phalcon.io/6.0/annotations/)
- Fixed `Phalcon\Encryption\Security\JWT\Token\Token::validate()` throwing `Phalcon\Encryption\Security\JWT\Exceptions\InvalidAudienceType` when handed a freshly constructed `Phalcon\Encryption\Security\JWT\Validator`, which made a default `Validator` impossible to pass to it. [#17361](https://github.com/phalcon/cphalcon/issues/17361) [[doc]](https://docs.phalcon.io/6.0/encryption-security-jwt/)
- Fixed `Phalcon\Encryption\Security\JWT\Validator::validateIssuedAt()` and `Phalcon\Encryption\Security\JWT\Validator::validateNotBefore()` rejecting a token whose `iat`/`nbf` claim falls on exactly the validated timestamp. [#17361](https://github.com/phalcon/cphalcon/issues/17361) [[doc]](https://docs.phalcon.io/6.0/encryption-security-jwt/)
- Fixed `Phalcon\Mvc\Model\Query::executeUpdate()` raising a PDO `Invalid parameter number: mixed named and positional parameters` error for a PHQL `UPDATE` whose `SET` clause is an expression carrying a bound placeholder (e.g. `SET col = col + :inc:`): the named placeholder is now resolved from the bind parameters and inlined into the expression before the `Phalcon\Db\RawValue` is built, so it no longer collides with the positional `?` marker of the primary-key `WHERE` clause, and the placeholder is removed from the bind parameters forwarded to the pre-update `SELECT`. [#16976](https://github.com/phalcon/cphalcon/issues/16976) [[doc]](https://docs.phalcon.io/6.0/db-models/)
- Fixed the PHP 8.4/8.5 deprecation notices raised by the framework: removed the `imagedestroy()` calls in `Phalcon\Image\Adapter\Gd` (a no-op since PHP 8.0), the `finfo_close()` calls in `Phalcon\Http\Request\File` and `Phalcon\Filter\Validation\Validator\File\MimeType` and the `ReflectionProperty::setAccessible()` call in `Phalcon\Support\Debug\Dump` (no-ops since PHP 8.1), clamped the random pad byte in `Phalcon\Encryption\Crypt\Padding\Iso10126` to `chr(rand() % 256)` to avoid the out-of-range `chr()` deprecation on PHP 8.5, and guarded `Phalcon\Messages\Messages::offsetSet()` against an implicit `null` array offset. [#17253](https://github.com/phalcon/cphalcon/issues/17253) [[doc]](https://docs.phalcon.io/6.0/)

### Removed

- Removed the deprecated `Serializable` interface from `Phalcon\Mvc\Model` and `Phalcon\Mvc\Model\Resultset` (deprecated by PHP 8.1); the `__serialize()` and `__unserialize()` magic methods remain, so model and resultset serialization is unchanged. [#17253](https://github.com/phalcon/cphalcon/issues/17253) [[doc]](https://docs.phalcon.io/6.0/db-models/)

## [6.0.0 alpha 3](https://github.com/phalcon/phalcon/releases/tag/v6.0.0alpha3) (2026-06-29)

### Changed

- Changed `Phalcon\Acl\Adapter\Memory` so a freshly constructed adapter returns an empty array instead of `null` from `getRoles()`, `getComponents()` and `getInheritedRoles()`. [#17220](https://github.com/phalcon/cphalcon/issues/17220) [[doc]](https://docs.phalcon.io/6.0/acl/)
- Deprecated `Phalcon\Acl\Adapter\Memory::getActiveKey()` (use `getActiveRole()`, `getActiveComponent()` and `getActiveAccess()`) and the legacy ACL interfaces `Phalcon\Acl\Adapter\AdapterInterface`, `Phalcon\Acl\RoleInterface`, `Phalcon\Acl\ComponentInterface`, `Phalcon\Acl\RoleAwareInterface` and `Phalcon\Acl\ComponentAwareInterface` in favour of their `Phalcon\Contracts\Acl\...` equivalents. [#17220](https://github.com/phalcon/cphalcon/issues/17220) [[doc]](https://docs.phalcon.io/6.0/acl/)
- Changed the `Phalcon\Auth` layer to throw granular `Phalcon\Auth\Exceptions\*` subclasses instead of the base `Phalcon\Auth\Exception`: `AccessNotRegistered`, `ActiveAccessRequired`, `DefaultGuardNotRegistered` and `GuardNotDefined` (`Phalcon\Auth\Manager`), `UnknownAdapter` and `UnknownGuard` (`Phalcon\Auth\ManagerFactory`), `OptionRequiresArray` and `OptionRequiresString` (`fromOptions()` option parsing), `SessionNamesMustDiffer` (`Phalcon\Auth\Guard\Config\SessionGuardConfig`), and `MissingHandlerContext` (`Phalcon\Auth\Access\Acl`). Each extends `Phalcon\Auth\Exception`, so existing `catch` blocks keep working. [#17220](https://github.com/phalcon/cphalcon/issues/17220) [[doc]](https://docs.phalcon.io/6.0/auth/)
- Changed the `Phalcon\Auth` array adapters (`Memory`, `Stream`) to compare non-password credential fields against configured row values as strings, so string input from a request (e.g. `'1'`) matches a typed row value (e.g. `1` or `true`) instead of failing a strict type comparison. [#17220](https://github.com/phalcon/cphalcon/issues/17220) [[doc]](https://docs.phalcon.io/6.0/auth/)
- Note: `Phalcon\Auth\ManagerFactory` validates the required guard configuration up front and throws a `Phalcon\Auth\Exception` subclass on a missing key, where earlier versions emitted a PHP notice followed by a `TypeError`; handlers that caught `TypeError` there should catch `Phalcon\Auth\Exception` instead. [#17220](https://github.com/phalcon/cphalcon/issues/17220) [[doc]](https://docs.phalcon.io/6.0/auth/)
- Changed `Phalcon\Support\Debug` into a thin coordinator that delegates exception-data collection to the new `Phalcon\Support\Debug\ReportBuilder` and HTML rendering to a `Phalcon\Contracts\Support\Debug\Renderer` (default `Phalcon\Support\Debug\Renderer\HtmlRenderer`), and exposes `getRenderer()`/`setRenderer()` to swap the renderer. [#17202](https://github.com/phalcon/cphalcon/issues/17202) [[doc]](https://docs.phalcon.io/6.0/support-debug/)
- Changed `Phalcon\Support\Debug` and `Phalcon\Support\Debug\Dump` to render from named, overridable template strings (the new `Phalcon\Contracts\Support\Debug\TemplateAware` contract with `getTemplate()`/`setTemplate()`) filled by the interpolator, instead of inline string concatenation. [#17202](https://github.com/phalcon/cphalcon/issues/17202) [[doc]](https://docs.phalcon.io/6.0/support-debug/)
- Changed the `Phalcon\Support\Debug` exception page to a redesigned, asset-driven layout (masthead with the Phalcon logo, error card, tabbed Request/Server/Included Files/Memory/Variables context, and collapsible backtrace frames); `getCssSources()` and `getJsSources()` now reference a single `debug.css` and `debug.js` instead of the bundled jQuery, jQuery-UI and prettify assets. [#17202](https://github.com/phalcon/cphalcon/issues/17202) [[doc]](https://docs.phalcon.io/6.0/support-debug/)
- Changed `Phalcon\Support\Debug::getVersion()` to return a compact version badge anchor (`v<version>`) instead of the previous "Phalcon Framework" version block. [#17202](https://github.com/phalcon/cphalcon/issues/17202) [[doc]](https://docs.phalcon.io/6.0/support-debug/)
- Changed the `Phalcon\Support\Debug` Memory panel to report both real and peak memory usage. [#17202](https://github.com/phalcon/cphalcon/issues/17202) [[doc]](https://docs.phalcon.io/6.0/support-debug/)
- Refactored the read path of model hydration by extracting `Phalcon\Mvc\Model::cloneResultMapHydrate()` into the dedicated `Phalcon\Mvc\Model\Hydration\CloneResultMapHydrate` class and the case-insensitive column-map lookup into `Phalcon\Mvc\Model\Hydration\CaseInsensitiveColumnMap`. [#17228](https://github.com/phalcon/cphalcon/issues/17228) [[doc]](https://docs.phalcon.io/6.0/db-models/)

### Added

- Added `Phalcon\Acl\Adapter\Storage`, a storage-backed ACL adapter that persists the entire policy as a versioned, serializer-agnostic snapshot to any `Phalcon\Storage` backend (Redis, Apcu, Stream, Memcached) and reloads it on construction, defined by the new `Phalcon\Contracts\Acl\Adapter\Persistable` contract (`save()`/`load()`). Callable (closure) rules are not serializable, so they are persisted as `DENY` (fail closed) and must be re-registered after `load()`; `load()` returns `false` for a snapshot without a version marker and throws `Phalcon\Acl\Exceptions\InvalidSnapshot` on an incompatible version or a malformed structure. Single-writer contract: `save()` writes the whole snapshot (last-write-wins; use external locking for concurrent writers). [#17220](https://github.com/phalcon/cphalcon/issues/17220) [[doc]](https://docs.phalcon.io/6.0/acl/)
- Added `Phalcon\Acl\Exceptions\InvalidSnapshot`, thrown by `Phalcon\Acl\Adapter\Storage::load()` on an incompatible or malformed policy snapshot. [#17220](https://github.com/phalcon/cphalcon/issues/17220) [[doc]](https://docs.phalcon.io/6.0/acl/)
- Added the `Phalcon\Contracts\Acl` contracts - `Phalcon\Contracts\Acl\Adapter\Adapter`, `Phalcon\Contracts\Acl\Adapter\Persistable`, `Phalcon\Contracts\Acl\Role`, `Phalcon\Contracts\Acl\Component`, `Phalcon\Contracts\Acl\RoleAware` and `Phalcon\Contracts\Acl\ComponentAware` - as the canonical homes for the ACL interfaces; the legacy `Phalcon\Acl\...\*Interface` types remain as deprecated bridges that extend them. [#17220](https://github.com/phalcon/cphalcon/issues/17220) [[doc]](https://docs.phalcon.io/6.0/acl/)
- Added the granular `Phalcon\Auth\Exceptions\*` exceptions `AccessNotRegistered`, `ActiveAccessRequired`, `DefaultGuardNotRegistered`, `GuardNotDefined`, `MissingHandlerContext`, `OptionRequiresArray`, `OptionRequiresString`, `SessionNamesMustDiffer`, `UnknownAdapter` and `UnknownGuard`. [#17220](https://github.com/phalcon/cphalcon/issues/17220) [[doc]](https://docs.phalcon.io/6.0/auth/)
- Added the `Phalcon\Contracts\Support\Debug\TemplateAware` and `Phalcon\Contracts\Support\Debug\Renderer` contracts, the `Phalcon\Support\Debug\ReportBuilder` and `Phalcon\Support\Debug\Renderer\HtmlRenderer` classes, the `Phalcon\Support\Debug\Traits\TemplateAwareTrait` trait, and the value objects `Phalcon\Support\Debug\Report\ExceptionReport` and `Phalcon\Support\Debug\Report\BacktraceItem`. [#17202](https://github.com/phalcon/cphalcon/issues/17202) [[doc]](https://docs.phalcon.io/6.0/support-debug/)
- Added `Phalcon\Support\Debug::getRenderer()` and `Phalcon\Support\Debug::setRenderer()`. [#17202](https://github.com/phalcon/cphalcon/issues/17202) [[doc]](https://docs.phalcon.io/6.0/support-debug/)
- Added the `Phalcon\Queue` component, a first-class queue/messaging layer modeled on the queue-interop contracts, with the `Phalcon\Contracts\Queue\*` interfaces (`ConnectionFactory`, `Context`, `Destination`, `Queue`, `Topic`, `Producer`, `Consumer`, `SubscriptionConsumer`, `Message`, `Processor`, `VisibilityAware`) and the `Phalcon\Queue\Exceptions\*` hierarchy (`QueueThrowable`, `Exception` and the typed `Invalid*` / `*NotSupportedException` exceptions). [#17051](https://github.com/phalcon/cphalcon/issues/17051) [[doc]](https://docs.phalcon.io/6.0/queue/)
- Added the Memory and Stream queue adapters (`Phalcon\Queue\Adapter\Memory\*`, in-process FIFO; `Phalcon\Queue\Adapter\Stream\*`, file-per-queue with `flock`). [#17051](https://github.com/phalcon/cphalcon/issues/17051) [[doc]](https://docs.phalcon.io/6.0/queue/)
- Added the Redis queue adapter (`Phalcon\Queue\Adapter\Redis\*`) with list-backed FIFO delivery (`LPUSH`/`BRPOP`), sorted-set delivery delay and native blocking receive. [#17051](https://github.com/phalcon/cphalcon/issues/17051) [[doc]](https://docs.phalcon.io/6.0/queue/)
- Added the Beanstalk queue adapter (`Phalcon\Queue\Adapter\Beanstalk\*`) over a dependency-free socket client, with native delivery delay and priority and a `VisibilityAware` consumer (`touch()`). [#17051](https://github.com/phalcon/cphalcon/issues/17051) [[doc]](https://docs.phalcon.io/6.0/queue/)
- Added the `Phalcon\Contracts\Queue\Inspectable` contract (`getStats(Queue $queue): array`), implemented by `Phalcon\Queue\Adapter\Beanstalk\BeanstalkContext`, exposing the Beanstalkd `stats-tube` fields (`current-jobs-ready`, `current-jobs-reserved`, `current-jobs-delayed`, `current-jobs-buried`, `current-jobs-urgent`, `total-jobs`, the `cmd-*` counters, ...) for queue backlog/depth monitoring. The returned array is adapter-native; the `current-jobs-*` keys are always present (zero for an unknown tube) and the read runs on its own short-lived connection. Backed by a new `Phalcon\Queue\Adapter\Beanstalk\BeanstalkConnection::statsTube()` wire command. [#17209](https://github.com/phalcon/cphalcon/issues/17209) [[doc]](https://docs.phalcon.io/6.0/queue/)
- Added the `Phalcon\Queue\AdapterFactory` and `Phalcon\Queue\QueueFactory` factories, and registered the `queueFactory` service in `Phalcon\Di\FactoryDefault` and `Phalcon\Di\FactoryDefault\Cli`. [#17051](https://github.com/phalcon/cphalcon/issues/17051) [[doc]](https://docs.phalcon.io/6.0/queue/)
- Added the queue consumer runner (`Phalcon\Queue\Consumer\QueueConsumer`, `Worker`, `WorkerOptions`, `BoundProcessor`, `Events`) and the CLI consumer task `Phalcon\Queue\Cli\ConsumerTask`. [#17051](https://github.com/phalcon/cphalcon/issues/17051) [[doc]](https://docs.phalcon.io/6.0/queue/)
- Added connection-liveness and opt-in auto-reconnect support to `Phalcon\Db\Adapter\Pdo\AbstractPdo`: `ping()` (a `SELECT 1` probe), `ensureConnection()` (reconnect in place when the probe fails), and `setAutoReconnect()`/`getAutoReconnect()` (also settable via the `autoReconnect` descriptor key). When auto-reconnect is enabled and a query fails on a lost ("gone away") connection outside a transaction, `execute()` and `query()` fire the new `db:connectionLost` event, reconnect, and retry the statement once; a loss inside a transaction is re-thrown without retry. "Gone away" detection is provided per driver by `Phalcon\Db\Adapter\Pdo\Mysql` (error codes 2006/2013) and `Phalcon\Db\Adapter\Pdo\Postgresql` (SQLSTATE 08003/08006/57P01-03), with a message fallback. [#17204](https://github.com/phalcon/cphalcon/issues/17204) [[doc]](https://docs.phalcon.io/6.0/db-layer/)
- Added the same liveness and opt-in auto-reconnect support to `Phalcon\DataMapper\Pdo\Connection` (`ping()`, `ensureConnection()`, `setAutoReconnect()`/`getAutoReconnect()`), wrapping `exec()`, `perform()`, `prepare()`, and `query()` with the single-retry behavior. This connection has no events manager, so no `db:connectionLost` event is fired; "gone away" detection is driver-agnostic and the in-transaction guard uses a locally tracked transaction level. [#17204](https://github.com/phalcon/cphalcon/issues/17204) [[doc]](https://docs.phalcon.io/6.0/db-layer/)

### Fixed

- Fixed `Phalcon\Auth` login timing leaking account existence: the credential adapters now perform a throwaway password hash on the user-not-found path, so an attempt for an unknown identifier costs the same as one for a real account with a wrong password (mitigates login-timing user enumeration). [#17220](https://github.com/phalcon/cphalcon/issues/17220) [[doc]](https://docs.phalcon.io/6.0/auth/)
- Fixed `Phalcon\Mvc\Model::cloneResultMap()` calling model setters during ORM hydration unconditionally (introduced in 5.12.0 via [#14810](https://github.com/phalcon/cphalcon/issues/14810)), which ran user setters on every record hydrated by `find()`/`findFirst()`; a setter that issued an ORM query (e.g. `self::findFirstByEmail()`) recursed infinitely, as `findFirst()` re-entered `cloneResultMap()`, which re-invoked the setter, which called `findFirst()` again. Hydration setters are now gated by a dedicated `orm.call_setters_on_hydration` setting (default `false`), decoupled from `orm.disable_assign_setters` (which still governs `assign()`); this restores the pre-5.12.0 hydration behavior by default and makes setter execution during hydration opt-in. [#17214](https://github.com/phalcon/cphalcon/issues/17214) [[doc]](https://docs.phalcon.io/6.0/db-models/)
- Fixed `Phalcon\Mvc\View\Engine\Volt\Compiler::resolveFilter()` building the `join` filter by splicing the raw separator and array argument token values into the generated PHP - the separator dropped between two literal single quotes and the array emitted unquoted - instead of compiling them through `expression()` as every other Volt literal is. A `join` separator literal containing a single quote closed the generated `join('...')` call and injected arbitrary statements into the compiled template, which the Volt engine then ran when the view was rendered. Both arguments are now emitted through `expression()`, which quotes and escapes the separator. [[doc]](https://docs.phalcon.io/6.0/volt/)
- Fixed `Phalcon\Support\Debug` ignoring the `request` entry of `setBlacklist()`: `$_REQUEST` is now filtered against the `request` blacklist, where previously both superglobals were filtered against the `server` blacklist only. [#17202](https://github.com/phalcon/cphalcon/issues/17202) [[doc]](https://docs.phalcon.io/6.0/support-debug/)
- Fixed `Phalcon\Tag\Select::selectField()` to invoke the resultset `using` render callback only when it is a `Closure` (previously any object), keeping the dynamically invoked callable out of reach of user-controlled data. [#17210](https://github.com/phalcon/cphalcon/issues/17210)
- Fixed `Phalcon\Forms\Element\AbstractElement::render()` to cast a non-`null` element value to `string` before passing it to the input helper, so a numeric default set via `setDefault()` (e.g. `setDefault(10)` or `setDefault(10.5)` on a `Phalcon\Forms\Element\Numeric`) renders as `value="10"` instead of raising a `TypeError` for passing an `int`/`float` to the helper's `string` `$value` parameter. [#17232](https://github.com/phalcon/cphalcon/issues/17232) [[doc]](https://docs.phalcon.io/6.0/forms/)
- Fixed `Phalcon\Http\Response::getStatusCode()` and `Phalcon\Http\Response::getReasonPhrase()` raising a `TypeError` (`substr(): Argument #1 ($string) must be of type string, bool given`) when no `Status` header had been set (e.g. a response built with only `setContent()`), because `Phalcon\Http\Response\Headers::get('Status')` returns `false` for an absent header; the header value is now cast to string before `substr()`, so both methods return `null` as documented. [#17248](https://github.com/phalcon/cphalcon/issues/17248) [[doc]](https://docs.phalcon.io/6.0/http-response/)

### Removed

## [6.0.0 alpha 2](https://github.com/phalcon/phalcon/releases/tag/v6.0.0alpha2) (2026-06-19)
## [6.0.0 alpha 1](https://github.com/phalcon/phalcon/releases/tag/v6.0.0alpha1) (2026-06-19)

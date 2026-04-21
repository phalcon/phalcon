# Changelog

## 6.0.0 alpha 1 (2026-XX-XX)

### Breaking Compatibility Changes

- Changed `Phalcon\Mvc\Dispatcher::getControllerName()` and `Phalcon\Mvc\Dispatcher::getPreviousControllerName()` to return the uncamelized (snake_case lowercase) controller name, restoring the behavior from Phalcon 4 and ensuring consistent output regardless of the casing used in route definitions [#CP-15996](https://github.com/phalcon/cphalcon/issues/15996)
- 
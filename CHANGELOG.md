# Changelog

## 6.0.0 alpha 1 (2026-XX-XX)

### Breaking Compatibility Changes

- Changed `Phalcon\Mvc\Dispatcher::getControllerName()` and `Phalcon\Mvc\Dispatcher::getPreviousControllerName()` to return the uncamelized (snake_case lowercase) controller name, restoring the behavior from Phalcon 4 and ensuring consistent output regardless of the casing used in route definitions [#CP-15996](https://github.com/phalcon/cphalcon/issues/15996)
- 

### Fixed

- Fixed `Phalcon\Html\Helper\Input\Checkbox::processChecked()` treating an empty-string `checked` attribute as absent (via `!empty()`), preventing `Checkbox` and `Radio` inputs with `value=""` from ever rendering `checked="checked"` [#16648](https://github.com/phalcon/cphalcon/issues/16648)
- Fixed `Phalcon\Mvc\Model\MetaData::writeMetaDataIndex()` to buffer writes when metadata has not yet been initialized, preventing `skipAttributes()` called in a parent model's `initialize()` from prematurely populating metadata for a child model with the wrong source table [#16544](https://github.com/phalcon/cphalcon/issues/16544)

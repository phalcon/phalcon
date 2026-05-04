# Changelog

## 6.0.0 alpha 1 (2026-XX-XX)

### Breaking Compatibility Changes

- Changed `Phalcon\Mvc\Dispatcher::getControllerName()` and `Phalcon\Mvc\Dispatcher::getPreviousControllerName()` to return the uncamelized (snake_case lowercase) controller name, restoring the behavior from Phalcon 4 and ensuring consistent output regardless of the casing used in route definitions [#CP-15996](https://github.com/phalcon/cphalcon/issues/15996)
- 

### Fixed

- Fixed `Phalcon\Html\Helper\Input\Checkbox::processChecked()` treating an empty-string `checked` attribute as absent (via `!empty()`), preventing `Checkbox` and `Radio` inputs with `value=""` from ever rendering `checked="checked"` [#16648](https://github.com/phalcon/cphalcon/issues/16648)
- Fixed `Phalcon\Mvc\Model\MetaData::writeMetaDataIndex()` to buffer writes when metadata has not yet been initialized, preventing `skipAttributes()` called in a parent model's `initialize()` from prematurely populating metadata for a child model with the wrong source table [#16544](https://github.com/phalcon/cphalcon/issues/16544)
- Fixed `Phalcon\Mvc\Model::doLowInsert()` throwing `Unable to insert into <table> without data` when saving a model whose only column is an auto-increment primary key; on dialects where `useExplicitIdValue()` is `false` (MySQL, SQLite) the identity branch produced an empty `values` array. The identity column is now added with the connection's default identity value when the resulting `values` array would otherwise be empty [#156](https://github.com/phalcon/phalcon/issues/156)
- Fixed `Phalcon\Mvc\Model\Query::executeUpdate()` and `Phalcon\Mvc\Model::doLowUpdate()` for PHQL `UPDATE ... SET <expr>` expressions with placeholders (e.g. `col = col + :inc:`): named placeholders embedded in expression SQL are now resolved before creating `RawValue` to avoid PDO "mixed named and positional parameters", and dynamic-update comparisons now always treat `RawValue` assignments as changed so updates are not skipped when the current numeric value is `0` [#16976](https://github.com/phalcon/cphalcon/issues/16976)

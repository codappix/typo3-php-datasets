# Changelog

## v2.0.0 - 2025-10-20

### Added

- Support for TYPO3 13.4.19.
  They modified `@internal` API and it is the fault of this package to use the API.
  We therefore now move to low level public API of doctrine/dbal instead of TYPO3 internal API.
  We also encapsulate the access to those APIs for easier maintenance.
  We also raise dev dependencies to have automated CI verification that we do not use internal APIs anymore.
- Proper errors if a record was found in DB, but not within assertions.
- Do not include `phpstan-baseline.neon` in git exports / distribution.

### BREAKING

- Remove support for older dependencies.
  The older versions of this package should work just fine.
  There is not much more to this package, so no need to stay compatible with all versions.

## v1.6.0 - 2025-03-04

### Added

- Support for PHP 8.4.

## v1.5.0 - 2024-02-05

### Added

- Support for TYPO3 v13.0.
- Support for PHP 8.3.

## v1.4.1 - 2023-11-09

### Added

- Support empty columns in CSV conversion.

## v1.4.0 - 2023-11-07

### Added

- Add command `convert-from-csv`.
- Mark as dev dependency.

## v1.3.1 - 2023-08-10

### Added

- Add Support for mm relations in assertions.

## v1.3.0 - 2023-05-11

### Added

- Add Support for TYPO3 v12.4.

## v1.2.0 - 2023-05-04

### Added

- Add PHP 7.2 support.

## v1.1.0 - 2023-04-12

### Added

- Add bin `typo3-php-datasets`.

- Add command `convert-from-xml`.

## v1.0.0 - 2023-04-11

_Initial release._

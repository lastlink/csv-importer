# Change Log
All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

## [1.0.6] - 2020-04-26

⚠️ Several changes in this release may break Firefly III's duplication detection. Be careful importing large batches.

### Added
- You can now navigate back and forth between steps.
- You can configure the importer to send email reports. Checkout `.env.example`.

### Changed
- ⚠️ When the destination of a withdrawal is empty, *or* the source of a deposit is empty, the CSV importer will substitute these values with `(no name)` as
 it used to do when the CSV importer was part of Firefly III itself.

### Deprecated
- Initial release.

### Removed
- Initial release.

### Fixed
- Initial release.

### Security
- Initial release.



## [1.0.5] - 2020-04-22

### Fixed
- [Issue 3268](https://github.com/firefly-iii/firefly-iii/issues/3268) Issue with asset management.
- [Issue 3271](https://github.com/firefly-iii/firefly-iii/issues/3271) Bad handing of debit/credit columns.
- [Issue 3279](https://github.com/firefly-iii/firefly-iii/issues/3279) Issue handling JSON.


## [1.0.4] - 2020-04-16

- [Issue 3266](https://github.com/firefly-iii/firefly-iii/issues/3266) Import loop due to bad bccomp call.
- Some code cleanup.

## [1.0.3] - 2020-04-13

- Fix issue with account selection.
- Fix issue with amounts.

## [1.0.2] - 2020-04-12

### Added
- Add ability to handle `TRUSTED_PROXIES` environment variable.

### Fixed
- [Issue 3253](https://github.com/firefly-iii/firefly-iii/issues/3253) Could not map values if the delimiter wasn't a comma.
- [Issue 3254](https://github.com/firefly-iii/firefly-iii/issues/3254) Better handling of strings.
- [Issue 3258](https://github.com/firefly-iii/firefly-iii/issues/3258) Better handling of existing accounts.
- Better error handling (500 errors will not make the importer loop).
- Fixed handling of specifics, thanks to @FelikZ

## [1.0.1] - 2020-04-10

### Fixed
- Call to `convertBoolean` with bad parameters.
- Catch exception where Firefly III returns the wrong account.
- Update minimum version for Firefly III to 5.2.0.

## [1.0.0] - 2020-04-10

This release was preceded by several alpha and beta versions:

- 1.0.0-alpha.1 on 2019-10-31
- 1.0.0-alpha.2 on 2020-01-03
- 1.0.0-alpha.3 on 2020-01-11
- 1.0.0-beta.1 on 2020-02-23
- 1.0.0-beta.2 on 2020-03-13
- 1.0.0-beta.3 on 2020-04-08

### Added
- Initial release.

### Changed
- Initial release.

### Deprecated
- Initial release.

### Removed
- Initial release.

### Fixed
- Initial release.

### Security
- Initial release.

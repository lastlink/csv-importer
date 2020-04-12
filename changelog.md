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

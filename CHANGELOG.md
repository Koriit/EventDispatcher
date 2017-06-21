# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## [Unreleased]
### Fixed
- `OverridenParameter` exception was not thrown if overridden parameters were nulls.

## 1.0.0 - 2017-06-16
### Changed
- Renamed library and repo from `Koriit\PHP-ED` to `Korrit\EventDispatcher` 
  to match actual namespaces.
- Priority can now only be non-negative integer.
- It is now impossible to pass `eventName`, `eventContext` or `eventDispatcher`
  in additional parameters array. An exception is thrown.

### Added
- Added `InvalidPriority` exception.
- Added `OverriddenParameter` exception.
- It's now possible to ignore the value returned by listener with context.
- It's now possible to stop the dispatchment with context.

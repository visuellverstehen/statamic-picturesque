# Changelog

All notable changes to this project will be documented in this file.

## [v2.1.0](https://github.com/visuellverstehen/statamic-picturesque/compare/v2.0.1...v2.1.0) - 2026-03-05

## What's Changed
* feat: Statamic v6 support by @el-schneider in https://github.com/visuellverstehen/statamic-picturesque/pull/26
* Add unit and feature tests by @simonerd in https://github.com/visuellverstehen/statamic-picturesque/pull/30

**Full Changelog**: https://github.com/visuellverstehen/statamic-picturesque/compare/v2.0.1...v2.1.0

## [v2.0.1](https://github.com/visuellverstehen/statamic-picturesque/compare/v2.0.0...v2.0.1) - 2025-12-04

## What's Fixed
* fix: correct auto-ratio calculation in size data parsing by @el-schneider in https://github.com/visuellverstehen/statamic-picturesque/pull/28

**Full Changelog**: https://github.com/visuellverstehen/statamic-picturesque/compare/v2.0.0...v2.0.1

## [v2.0.0](https://github.com/visuellverstehen/statamic-picturesque/compare/v1.5.0...v2.0.0) - 2025-08-28

## What's New
* Add `width` and `height` attributes to img and sources by @el-schneider in https://github.com/visuellverstehen/statamic-picturesque/pull/23

_As these changes will produce a slightly different rendered output and that could potentially (depending on how Picturesque is used) result in a broken layout, we decided to publish this as a major release._

**Full Changelog**: https://github.com/visuellverstehen/statamic-picturesque/compare/v1.5.0...v2.0.0

## [v1.5.0](https://github.com/visuellverstehen/statamic-picturesque/compare/v1.4.7...v1.5.0) - 2025-08-28

## What's new
* Add support for Glide parameters by @el-schneider in https://github.com/visuellverstehen/statamic-picturesque/pull/22
* Add config for default glide_fit parameter by @simonerd in https://github.com/visuellverstehen/statamic-picturesque/pull/25

## What's fixed
* Remove composer.lock by @florianbrinkmann in https://github.com/visuellverstehen/statamic-picturesque/pull/20

## New Contributors
* @el-schneider made their first contribution in https://github.com/visuellverstehen/statamic-picturesque/pull/22

**Full Changelog**: https://github.com/visuellverstehen/statamic-picturesque/compare/v1.4.7...v1.5.0

## [v1.4.7](https://github.com/visuellverstehen/statamic-picturesque/compare/v1.4.6...v1.4.7) - 2024-10-08

## What's Changed
* feat: pass inline styles by @juliawarnke in https://github.com/visuellverstehen/statamic-picturesque/pull/19

**Full Changelog**: https://github.com/visuellverstehen/statamic-picturesque/compare/v1.4.6...v1.4.7

## [v1.4.6](https://github.com/visuellverstehen/statamic-picturesque/compare/v1.4.5...v1.4.6) - 2024-05-17

## What's Changed
* Add support for Statamic 5 by @doriengr in https://github.com/visuellverstehen/statamic-picturesque/pull/18

## New Contributors
* @doriengr made their first contribution in https://github.com/visuellverstehen/statamic-picturesque/pull/18

**Full Changelog**: https://github.com/visuellverstehen/statamic-picturesque/compare/v1.4.5...v1.4.6

## [v1.4.5](https://github.com/visuellverstehen/statamic-picturesque/compare/v1.4.4...v1.4.5) - 2024-03-04

## What's Changed
* Provide getAsset method by @juliawarnke in https://github.com/visuellverstehen/statamic-picturesque/pull/15

**Full Changelog**: https://github.com/visuellverstehen/statamic-picturesque/compare/v1.4.4...v1.4.5

## [v1.4.4](https://github.com/visuellverstehen/statamic-picturesque/compare/v1.4.3...v1.4.4) - 2024-02-26

## What's improved?

- Handling of picture parameters (e. g. alt text) can now be overwritten to use custom logic (see #13 for context)

## [v1.4.3](https://github.com/visuellverstehen/statamic-picturesque/compare/v1.4.2...v1.4.3) - 2024-02-20

## What's improved?

- Allow passing on `{{ picture }}` tag context to the under-the-hood glide tag

## What's fixed?

- Fixed a bug with asset meta values potentially being `null`

## [v1.4.2](https://github.com/visuellverstehen/statamic-picturesque/compare/v1.4.1...v1.4.2) - 2024-02-06

## What's Changed
* Remove trailing slash for void elements by @juliawarnke in https://github.com/visuellverstehen/statamic-picturesque/pull/9
* Provide integers for height and width attribute in image tag by @juliawarnke in https://github.com/visuellverstehen/statamic-picturesque/pull/10

**Full Changelog**: https://github.com/visuellverstehen/statamic-picturesque/compare/v1.4.1...v1.4.2

## [v1.4.1](https://github.com/visuellverstehen/statamic-picturesque/compare/v1.4.0...v1.4.1) - 2024-01-31

## What's fixed?

- Fixed a bug in srcset rendering that was introduced with the file format changes in 1.4.0 #8

## [v1.4.0](https://github.com/visuellverstehen/statamic-picturesque/compare/v1.3.0...v1.4.0) - 2024-01-26

## What's new?

- Generate sources for multiple filetypes, both by default and with local overwrites #7
- Add css classes to the `picture` element itself, not just to the `img` element #6

The readme was updated with instructions for both changes.

## What's improved?

- Improved error handling when supplying an invalid source.

## [v1.3.0](https://github.com/visuellverstehen/statamic-picturesque/compare/v1.2.1...v1.3.0) - 2023-06-20

## What's new?

- Added the ability to use portrait images
- Added the assets' original dimensions as width/height attributes on the `img` element

## [v1.2.1](https://github.com/visuellverstehen/statamic-picturesque/compare/v1.2.0...v1.2.1) - 2023-05-30

## What's Changed
* chore: make sure provided data is already of type asset by @juliawarnke in https://github.com/visuellverstehen/statamic-picturesque/pull/2

## New Contributors
* @juliawarnke made their first contribution in https://github.com/visuellverstehen/statamic-picturesque/pull/2

**Full Changelog**: https://github.com/visuellverstehen/statamic-picturesque/compare/v.1.2.0...v1.2.1

## [v1.2.0](https://github.com/visuellverstehen/statamic-picturesque/compare/v1.1.0...v1.2.0) - 2023-05-24

## What's new?
* Added a new config option `alt_fullstop` (bool, default `false`) to ensure alt texts end with a full stop

## What's changed?
* fix: use correct `asset` property instead of `sourceAsset` in case of filetype without glide support by @florianbrinkmann in https://github.com/visuellverstehen/statamic-picturesque/pull/1

## New contributors
* @florianbrinkmann made their first contribution in https://github.com/visuellverstehen/statamic-picturesque/pull/1

**Full Changelog**: https://github.com/visuellverstehen/statamic-picturesque/compare/v1.1.0...v.1.2.0

## [v1.1.0](https://github.com/visuellverstehen/statamic-picturesque/compare/v1.0.0...v1.1.0) - 2023-05-17

## What's improved?

- Image alt text now has all html tags removed
- The "how to use" section of the readme covers the simplest use cases a bit better

## [v1.0.0](https://github.com/visuellverstehen/statamic-picturesque/compare/v0.3.0-beta...v1.0.0) - 2023-04-13

As this package is now used in production in several projects already, we figured there's no reason to call it `beta` anymore.

## What's new?

- Added support for Statamic v4

## [v0.3.0-beta](https://github.com/visuellverstehen/statamic-picturesque/compare/v0.2.0-beta...v0.3.0-beta) - 2023-03-22

In addition to using Picturesque as an Antlers tag, you can now also use the base class. We added some info on how to do this in the readme.

## What's new

- Refactored the picture logic into a base class that can be used outside of an Antlers tag.
- Added a config option for the default filetype for image processing.

## What's improved

- Clarified the description of some config options.

## [v0.2.0-beta](https://github.com/visuellverstehen/statamic-picturesque/compare/v0.1.1-beta...v0.2.0-beta) - 2023-03-14

### What's new
- Add config for lazy loading and smallest generated image width

### What's improved
- Make sure glide urls are not generated more often than necessary
- Clarify details and fix spelling in readme

## [v0.1.1-beta](https://github.com/visuellverstehen/statamic-picturesque/compare/v0.1.0-beta...v0.1.1-beta) - 2023-03-10

- update composer.lock, which happened to be not up to date 🙈

## v0.1.0-beta - 2023-03-10

- Initial beta release
- Uses the most recent version of our `{{ picture }}` tag, as used in various projects
- Added all available information within the `README.md` file

# Changelog
All notable changes to this project will be documented in this file.

## [1.4.0] - 2021-08-31

- Added: support for php 8

## [1.3.0] - 2021-08-30
- Added: custom-Option to teaserLinkText field which will show a user input field to set a custom link text

## [1.2.0] - 2021-07-01
- Added: Polish translation

## [1.1.4] - 2020-08-24
- fixed autowiring issue
- renamed data container listener service

## [1.1.3] - 2020-08-24
- fixed migration command not avaiable in contao 4.9 (#9)
- fixed ContentListener autowiring

## [1.1.2] - 2020-08-10
- removed alt-attribute from ce_linkteaser link
- removed title-attribute from span
- added aria-label to ce_linkteaser link

## [1.1.1] - 2020-04-08
- fixed error in partials_linkteaser_image
- an html comment is now added in dev mode if show more is false (source entity was not found or hook returned false)

## [1.1.0] - 2020-04-08
- linkteaser now respects overwriteMeta.imageUrl, if element is not set to linkAll

## [1.0.1] - 2020-01-22
- added alt-attribute to ce_linkteaser link
- fixed an exception in backend when overwriting teaser template from an template theme folder (`/templates/<theme>/`) (#5)

## [1.0.0] - 2019-10-14

### Changed
* BREAKING: use already existing article db field instead of articleId -> use the migration command for update your db

#### Added
* migration command


## [0.3.2] - 2018-03-20

#### Changed
* enable `target="_blank"` for all link types

## [0.3.1] - 2018-03-19

#### Changed
* added some polish translations

## [0.3.0] - 2018-03-14

#### Changed
* linkTitle in LinkTeaserElement is now pageTitle if set

### Fixed
* removed some deprecation warnings
* removed unused imports

## [0.2.1] - 2018-09-11

#### Fixed
* issue #3: `ContentListener` did not get `ContaoFrameworkInterface` invoked

## [0.2.0] - 2018-09-04

#### Added
* `rel="noopener"` for target="_blank" links (see: https://developers.google.com/web/tools/lighthouse/audits/noopener for more information)

## [0.1.3] - 2018-08-20

#### Fixed
* Contao 4.5 compatibility

## [0.1.2] - 2018-08-20

#### Fixed
* debug code in service.yml

## [0.1.1] - 2018-08-16

#### Changed
* added missing composer dependency

## [0.1.0] - 2018-08-14

Initial version



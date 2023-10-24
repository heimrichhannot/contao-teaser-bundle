# Changelog
All notable changes to this project will be documented in this file.

## [2.0.12] - 2023-10-24
- Fixed: issue in page teaser migration

## [2.0.11] - 2023-06-06
- Fixed: warning in backend

## [2.0.10] - 2023-03-14
- Fixed: exception in CePageteaserMigration

## [2.0.9] - 2023-01-11
- Fixed: warning with php 8

## [2.0.8] - 2022-11-04
- Fixed: page teaser migration always triggers in some circumstances

## [2.0.7] - 2022-07-22
- Fixed: missing backend fields

## [2.0.6] - 2022-07-12
- Fixed: return onload callback when data container id is not set

## [2.0.5] - 2022-05-31
- Fixed: migration lead to exception with older doctrine versions

## [2.0.4] - 2022-05-23
- Fixed: migration leads to exception on empty databases

## [2.0.3] - 2022-04-20
- Fixed: Typo in linkteaser templates "lable" to "label" 

## [2.0.2] - 2022-03-28
- Fixed: InvalidFieldNameException at migration in some cases
- Fixed: missing doctrine/dbal dependency

## [2.0.1] - 2022-03-02
- Fixed: syntax error ([#15])

## [2.0.0] - 2022-03-01
- Added: ce_page_teaser and teaser module contao migrations
- Changed: minimum php version is now 7.4
- Changed: minimum contao version is now 4.9
- Changed: refactored some code 
- Changed: renamed Bundle class
- Removed: MigrationCommand

## [1.5.0] - 2021-10-18

- Added: headline partial template
- Added: headline-block to linkteaser_content_image templates

## [1.4.2] - 2021-10-15

- Fixed: palette issues with source field

## [1.4.1] - 2021-09-28
- Fixed: palette issues with teaserLinkText (see [#12],[#13])

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


[#15]: https://github.com/heimrichhannot/contao-teaser-bundle/issues/15
[#13]: https://github.com/heimrichhannot/contao-teaser-bundle/issues/13
[#12]: https://github.com/heimrichhannot/contao-teaser-bundle/issues/12

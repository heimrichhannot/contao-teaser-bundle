# Upgrade

This file contains information concerning upgrading from older version.

## From 1.x

- Renamed Bundle class from HeimrichHannotContaoTeaserBundle to HeimrichHannotTeaserBundle
- Renamed ContentListener to ContentContainer
- Replaced Migration Command with Contao Migrations
- Raised php and contao dependencies

## From module and version 0.x

The database field 'articleId' was dropped and 'article' is used instead (already existing field in the contao core).

* use migration command after update to get automatic migration

## From contao-ce_page_teaser module

This bundle uses an different content element type and another database structure.

* use migration command to update the database
* adapt template files


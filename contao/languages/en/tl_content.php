<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2016 Heimrich & Hannot GmbH
 *
 * @author  Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

use HeimrichHannot\ContaoTeaserBundle\ContentElement\LinkTeaserElement;

$lang = &$GLOBALS['TL_LANG']['tl_content'];

/**
 * Fields
 */
$lang['source'][0] = 'Redirect target';
$lang['source'][1] = 'Here you can override the default redirect target.';
$lang['jumpTo'][0] = 'Redirect page';
$lang['jumpTo'][1] = 'Please choose the page to which visitors will be redirected when clicking the teaser item.';
$lang['articleId'][0] = 'Article';
$lang['articleId'][1] = 'Please choose the article to which visitors will be redirected when clicking the teaser item.';
$lang['fileSRC'][0] = 'File source';
$lang['fileSRC'][1] = 'Please choose the file source to which visitors will be redirected when clicking the teaser item.';

$lang['teaserLinkText'][0] = '"More" link text';
$lang['teaserLinkText'][1] = 'Please choose a text for the "more" link.';
$lang['teaserLinkCssClass'][0] = '"More" link CSS classes';
$lang['teaserLinkCssClass'][1] = 'Here you can specify one or more CSS classes for the "more" link.';
$lang['teaserLinkBehaviour'][0] = '"More" link behaviour';
$lang['teaserLinkBehaviour'][1] = 'Control the behaviour of the "more" link and teaser element.';
$lang['teaserContentTemplate'][0] = 'Overwrite teaser template';
$lang['teaserContentTemplate'][1] = 'Overwrite the template for the content of the teaser.';

$lang['linkTitle']['huh_teaser'] = 'Enter a custom "more" link text here. You can use the placeholder %title% (the title of the linked element).';
$lang['titleText']['huh_teaser'] = 'Enter a custom link title (title attribute) here. You can use the placeholders %title% (the title of the linked element) and %link% (the specified link text).';
$lang['teaserAriaLabel'][0] = 'Aria label';
$lang['teaserAriaLabel'][1] = 'Enter a custom aria label here. You can use the placeholders %title% (the title of the linked element) and %link% (the specified link text).';

/**
 * Legends
 */
$lang['teaser_legend']= 'Teaser settings';

/**
 * References
 */
$lang['reference']['source'][LinkTeaserElement::SOURCE_PAGE] = 'Page';
$lang['reference']['source']['file'] = 'File';
$lang['reference']['source']['download'] = 'Download';
$lang['reference']['source'][LinkTeaserElement::SOURCE_ARTICLE] = 'Article';
$lang['reference']['source']['external'] = 'External URL';

$lang['reference']['teaserLinkBehaviour']['default'] = 'Default - Display link';
$lang['reference']['teaserLinkBehaviour']['linkAll'] = 'Link entire element and show link';
$lang['reference']['teaserLinkBehaviour']['hideLink'] = 'Link entire element and hide link';

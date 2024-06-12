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
$lang['source'][0] = 'Weiterleitungsziel';
$lang['source'][1] = 'Hier können Sie die Weiterleitung festlegen.';
$lang['jumpTo'][0] = 'Weiterleitungsseite';
$lang['jumpTo'][1] = 'Bitte wählen Sie die Seite aus, zu der Besucher weitergeleitet werden, wenn Sie einen Beitrag anklicken.';
$lang['articleId'][0] = 'Artikel';
$lang['articleId'][1] = 'Bitte wählen Sie den Artikel aus, zu der Besucher weitergeleitet werden, wenn Sie einen Beitrag anklicken.';
$lang['fileSRC'][0] = 'Datei-Quelle';
$lang['fileSRC'][1] = 'Bitte wählen Sie eine Datei aus der Dateiverwaltung aus.';

$lang['teaserLinkText'][0] = 'Weiterlesen-Link Text';
$lang['teaserLinkText'][1] = 'Wählen Sie den Link-Text aus.';

$lang['teaserCustomLinkText'][0] = 'Benutzerdefinierter Weiterlesen-Link Text';
$lang['teaserCustomLinkText'][1] = 'Geben Sie hier einen benutzerdefinierten Weiterlesen-Link Text an. Sie können darüber hinaus Aria-Label und Tile-Attribute überschreiben';
$lang['teaserLinkCssClass'][0] = 'Weiterlesen-Link CSS-Klasse';
$lang['teaserLinkCssClass'][1] = 'Hier können Sie eine oder mehrer CSS-Klassen für den Link angeben.';
$lang['teaserLinkBehaviour'][0] = 'Weiterlesen-Link-Verhalten';
$lang['teaserLinkBehaviour'][1] = 'Steuern Sie das verhalten des Weiterlesen-Link und Teaser-Elements.';
$lang['teaserContentTemplate'][0] = 'Teaser-Template überschreiben';
$lang['teaserContentTemplate'][1] = 'Überschreiben Sie hier das Template für den Inhalt des Teasers.';

$lang['linkTitle']['huh_teaser'] = 'Geben Sie hier einen benutzerdefinierten Weiterlesen-Link Text an. Sie können den Platzhalteer %title% (der Titel des verlinkten Elementes) verwenden.';
$lang['titleText']['huh_teaser'] = 'Geben Sie hier einen benutzerdefinierten Link-Title (title-Attribut) an. Sie können den Platzhalt %title% (der Titel des verlinkten Elementes) und %link% (der angegebene Link-Text) verwenden.';
$lang['teaserAriaLabel'][0] = 'Aria-Label';
$lang['teaserAriaLabel'][1] = 'Geben Sie hier ein benutzerdefiniertes Aria-Label an. Sie können die Platzhalter %title% (der Titel des verlinkten Elementes) und %link% (der angegebene Link-Text) verwenden.';

/**
 * Legends
 */
$lang['teaser_legend']= 'Teaser-Einstellungen';

/**
 * References
 */
$lang['reference']['source'][LinkTeaserElement::SOURCE_PAGE] = 'Seite';
$lang['reference']['source']['file'] = 'Datei';
$lang['reference']['source']['download'] = 'Download';
$lang['reference']['source'][LinkTeaserElement::SOURCE_ARTICLE] = 'Artikel';
$lang['reference']['source']['external'] = 'Externe URL';

$lang['reference']['teaserLinkBehaviour']['default'] = 'Standard - Link anzeigen';
$lang['reference']['teaserLinkBehaviour']['linkAll'] = 'Gesamtes Element verlinken und Link anzeigen';
$lang['reference']['teaserLinkBehaviour']['hideLink'] = 'Gesamtes Element verlinken und Link verstecken';

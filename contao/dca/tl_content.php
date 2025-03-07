<?php

/**
 * Contao Open Source CMS.
 *
 * Copyright (c) 2016 Heimrich & Hannot GmbH
 *
 * @author  Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

use HeimrichHannot\ContaoTeaserBundle\ContentElement\LinkTeaserElement;

$dc = &$GLOBALS['TL_DCA']['tl_content'];

/*
 * Palettes
 */
$dc['palettes'][LinkTeaserElement::TYPE] =
    '{type_legend},type,headline;
    {teaser_legend},source,teaserLinkText,teaserLinkCssClass,teaserLinkBehaviour,teaserContentTemplate,target;
    {text_legend},text;
    {image_legend},addImage;
    {template_legend:hide},customTpl;
    {protected_legend:hide},protected;
    {expert_legend:hide},guests,cssID,space;
    {invisible_legend:hide},invisible,start,stop';

/*
 * Subpalettes
 */
$dc['subpalettes']['source_page'] = 'jumpTo';
$dc['subpalettes']['source_file'] = 'fileSRC';
$dc['subpalettes']['source_download'] = 'fileSRC';
$dc['subpalettes']['source_article'] = 'article';
$dc['subpalettes']['source_external'] = 'url';

/**
 * Fields.
 */
$arrFields = [
    'source' => [
        'default' => LinkTeaserElement::SOURCE_PAGE,
        'exclude' => true,
        'filter' => true,
        'inputType' => 'radio',
        'reference' => &$GLOBALS['TL_LANG']['tl_content']['reference']['source'],
        'eval' => [
            'submitOnChange' => true,
            'helpwizard' => true,
            'mandatory' => true,
        ],
        'sql' => "varchar(12) NOT NULL default ''",
    ],
    'jumpTo' => [
        'label' => &$GLOBALS['TL_LANG']['tl_content']['jumpTo'],
        'exclude' => true,
        'inputType' => 'pageTree',
        'foreignKey' => 'tl_page.title',
        'eval' => [
            'mandatory' => true,
            'fieldType' => 'radio',
        ],
        'sql' => "int(10) unsigned NOT NULL default '0'",
        'relation' => [
            'type' => 'belongsTo',
            'load' => 'lazy',
        ],
    ],
    'fileSRC' => [
        'label' => &$GLOBALS['TL_LANG']['tl_content']['fileSRC'],
        'exclude' => true,
        'inputType' => 'fileTree',
        'eval' => [
            'filesOnly' => true,
            'fieldType' => 'radio',
            'mandatory' => true,
            'tl_class' => 'clr',
        ],
        'sql' => 'binary(16) NULL',
    ],
    'teaserLinkText' => [
        'label' => &$GLOBALS['TL_LANG']['tl_content']['teaserLinkText'],
        'exclude' => true,
        'search' => false,
        'inputType' => 'select',
        'eval' => [
            'tl_class' => 'w50 clr',
            'maxlength' => 64,
            'submitOnChange' => true,
        ],
        'sql' => "varchar(64) NOT NULL default ''",
    ],
    'teaserAriaLabel' => [
        'exclude' => true,
        'inputType' => 'text',
        'eval' => [
            'maxlength' => 255,
            'tl_class' => 'w50',
        ],
        'sql' => "varchar(255) NOT NULL default ''",
    ],
    'teaserLinkCssClass' => [
        'label' => &$GLOBALS['TL_LANG']['tl_content']['teaserLinkCssClass'],
        'exclude' => true,
        'inputType' => 'text',
        'eval' => [
            'tl_class' => 'w50 clr',
            'maxlength' => 64,
        ],
        'sql' => "varchar(64) NOT NULL default ''",
    ],
    'teaserLinkBehaviour' => [
        'label' => &$GLOBALS['TL_LANG']['tl_content']['teaserLinkBehaviour'],
        'exclude' => true,
        'inputType' => 'select',
        'default' => 'default',
        'options' => [
            LinkTeaserElement::LINK_BEHAVIOUR_SHOW_LINK,
            LinkTeaserElement::LINK_BEHAVIOUR_LINK_ALL,
            LinkTeaserElement::LINK_BEHAVIOUR_HIDE_LINK,
        ],
        'reference' => &$GLOBALS['TL_LANG']['tl_content']['reference']['teaserLinkBehaviour'],
        'eval' => [
            'tl_class' => 'w50 clr',
        ],
        'sql' => "varchar(32) NOT NULL default ''",
    ],
    'teaserContentTemplate' => [
        'label' => &$GLOBALS['TL_LANG']['tl_content']['teaserContentTemplate'],
        'exclude' => true,
        'inputType' => 'select',
        'eval' => [
            'tl_class' => 'w50',
            'includeBlankOption' => true,
        ],
        'sql' => "varchar(64) NOT NULL default ''",
    ],
];

$dc['fields'] = array_merge($dc['fields'], $arrFields);

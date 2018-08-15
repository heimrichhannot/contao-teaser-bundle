<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2016 Heimrich & Hannot GmbH
 *
 * @author  Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

$dc = &$GLOBALS['TL_DCA']['tl_content'];

$dc['config']['onload_callback'][] = ['huh.teaser.listener.dc.content', 'modifyPalette'];

/**
 * Selector
 */
array_insert($dc['palettes']['__selector__'], 0, 'source');

/**
 * Palettes
 */
$dc['palettes'][\HeimrichHannot\ContaoTeaserBundle\ContentElement\LinkTeaserElement::TYPE] =
    '{type_legend},type,headline;
    {teaser_legend},source,teaserLinkText,teaserLinkCssClass,teaserLinkBehaviour,teaserContentTemplate;
    {text_legend},text;
    {image_legend},addImage;
    {template_legend:hide},customTpl;
    {protected_legend:hide},protected;
    {expert_legend:hide},guests,cssID,space;
    {invisible_legend:hide},invisible,start,stop';

/**
 * Subpalettes
 */
$dc['subpalettes']['source_page']     = 'jumpTo';
$dc['subpalettes']['source_file']     = 'fileSRC';
$dc['subpalettes']['source_download'] = 'fileSRC';
$dc['subpalettes']['source_article']  = 'articleId';
$dc['subpalettes']['source_external'] = 'url,target';


/**
 * Fields
 */
$arrFields = [
    'source'                => [
        'label'            => &$GLOBALS['TL_LANG']['tl_content']['source'],
        'default'          => 'page',
        'exclude'          => true,
        'filter'           => true,
        'inputType'        => 'radio',
        'options_callback' => ['huh.teaser.listener.dc.content', 'getSourceOptions'],
        'reference'        => &$GLOBALS['TL_LANG']['tl_content']['reference']['source'],
        'eval'             => ['submitOnChange' => true, 'helpwizard' => true, 'mandatory' => true],
        'sql'              => "varchar(12) NOT NULL default ''",
    ],
    'jumpTo'                => [
        'label'      => &$GLOBALS['TL_LANG']['tl_content']['jumpTo'],
        'exclude'    => true,
        'inputType'  => 'pageTree',
        'foreignKey' => 'tl_page.title',
        'eval'       => ['mandatory' => true, 'fieldType' => 'radio'],
        'sql'        => "int(10) unsigned NOT NULL default '0'",
        'relation'   => ['type' => 'belongsTo', 'load' => 'lazy'],
    ],
    'fileSRC'               => [
        'label'         => &$GLOBALS['TL_LANG']['tl_content']['fileSRC'],
        'exclude'       => true,
        'inputType'     => 'fileTree',
        'eval'          => ['filesOnly' => true, 'fieldType' => 'radio', 'mandatory' => true, 'tl_class' => 'clr'],
        'load_callback' => [
            ['huh.teaser.listener.dc.content', 'setFileSrcFlags'],
        ],
        'sql'           => "binary(16) NULL",
    ],
    'articleId'             => [
        'label'            => &$GLOBALS['TL_LANG']['tl_content']['articleId'],
        'exclude'          => true,
        'inputType'        => 'select',
        'options_callback' => ['huh.teaser.listener.dc.content', 'getArticleAlias'],
        'eval'             => ['chosen' => true, 'mandatory' => true],
        'sql'              => "int(10) unsigned NOT NULL default '0'",
    ],
    'teaserLinkText'        => [
        'label'            => &$GLOBALS['TL_LANG']['tl_content']['teaserLinkText'],
        'exclude'          => true,
        'search'           => true,
        'inputType'        => 'select',
        'options_callback' => ['huh.teaser.listener.dc.content', 'getTeaserLinkText'],
        'eval'             => ['tl_class' => 'w50 clr', 'maxlength' => 64],
        'sql'              => "varchar(64) NOT NULL default ''",
    ],
    'teaserLinkCssClass'    => [
        'label'     => &$GLOBALS['TL_LANG']['tl_content']['teaserLinkCssClass'],
        'exclude'   => true,
        'inputType' => 'text',
        'eval'      => ['tl_class' => 'w50', 'maxlength' => 64],
        'sql'       => "varchar(64) NOT NULL default ''",
    ],
    'teaserLinkBehaviour'   => [
        'label'     => &$GLOBALS['TL_LANG']['tl_content']['teaserLinkBehaviour'],
        'exclude'   => true,
        'inputType' => 'select',
        'default'   => 'default',
        'options'   => ['default', 'linkAll', 'hideLink'],
        'reference' => &$GLOBALS['TL_LANG']['tl_content']['reference']['teaserLinkBehaviour'],
        'eval'      => ['tl_class' => 'w50 clr'],
        'sql'       => "varchar(32) NOT NULL default ''",
    ],
    'teaserContentTemplate' => [
        'label'            => &$GLOBALS['TL_LANG']['tl_content']['teaserContentTemplate'],
        'exclude'          => true,
        'inputType'        => 'select',
        'options_callback' => ['huh.teaser.listener.dc.content', 'getTeaserContentTemplates'],
        'eval'             => ['tl_class' => 'w50', 'includeBlankOption' => true],
        'sql'              => "varchar(64) NOT NULL default ''",
    ],
];

$dc['fields'] = array_merge($dc['fields'], $arrFields);
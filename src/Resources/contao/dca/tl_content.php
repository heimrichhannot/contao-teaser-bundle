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

$dc['config']['onload_callback'][] = [HeimrichHannot\ContaoTeaserBundle\DataContainer\ContentListener::class, 'onLoad'];

/**
 * Selector
 */
array_insert($dc['palettes']['__selector__'], 0, 'source');

/**
 * Palettes
 */
$dc['palettes'][\HeimrichHannot\ContaoTeaserBundle\ContentElement\LinkTeaserElement::TYPE] =
    '{type_legend},type,headline;
    {teaser_legend},source,teaserLinkText,teaserLinkCssClass,teaserLinkBehaviour,teaserContentTemplate,target;
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
$dc['subpalettes']['source_article']  = 'article';
$dc['subpalettes']['source_external'] = 'url';


/**
 * Fields
 */
$arrFields = [
    'source'                => [
        'label'            => &$GLOBALS['TL_LANG']['tl_content']['source'],
        'default'          => \HeimrichHannot\ContaoTeaserBundle\ContentElement\LinkTeaserElement::SOURCE_PAGE,
        'exclude'          => true,
        'filter'           => true,
        'inputType'        => 'radio',
        'options_callback' => [HeimrichHannot\ContaoTeaserBundle\DataContainer\ContentListener::class, 'getSourceOptions'],
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
            [HeimrichHannot\ContaoTeaserBundle\DataContainer\ContentListener::class, 'setFileSrcFlags'],
        ],
        'sql'           => "binary(16) NULL",
    ],
    'teaserLinkText'        => [
        'label'            => &$GLOBALS['TL_LANG']['tl_content']['teaserLinkText'],
        'exclude'          => true,
        'search'           => true,
        'inputType'        => 'select',
        'options_callback' => [HeimrichHannot\ContaoTeaserBundle\DataContainer\ContentListener::class, 'getTeaserLinkText'],
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
        'options'   => [
            \HeimrichHannot\ContaoTeaserBundle\ContentElement\LinkTeaserElement::LINK_BEHAVIOUR_SHOW_LINK,
            \HeimrichHannot\ContaoTeaserBundle\ContentElement\LinkTeaserElement::LINK_BEHAVIOUR_LINK_ALL,
            \HeimrichHannot\ContaoTeaserBundle\ContentElement\LinkTeaserElement::LINK_BEHAVIOUR_HIDE_LINK
        ],
        'reference' => &$GLOBALS['TL_LANG']['tl_content']['reference']['teaserLinkBehaviour'],
        'eval'      => ['tl_class' => 'w50 clr'],
        'sql'       => "varchar(32) NOT NULL default ''",
    ],
    'teaserContentTemplate' => [
        'label'            => &$GLOBALS['TL_LANG']['tl_content']['teaserContentTemplate'],
        'exclude'          => true,
        'inputType'        => 'select',
        'options_callback' => [HeimrichHannot\ContaoTeaserBundle\DataContainer\ContentListener::class, 'getTeaserContentTemplates'],
        'eval'             => ['tl_class' => 'w50', 'includeBlankOption' => true],
        'sql'              => "varchar(64) NOT NULL default ''",
    ],
];

$dc['fields'] = array_merge($dc['fields'], $arrFields);
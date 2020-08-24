<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @author  Thomas Körner <t.koerner@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */


namespace HeimrichHannot\ContaoTeaserBundle\DataContainer;


use Contao\Backend;
use Contao\BackendUser;
use Contao\Config;
use Contao\ContentModel;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\DataContainer;
use Contao\System;
use HeimrichHannot\ContaoTeaserBundle\ContentElement\LinkTeaserElement;

class ContentListener
{
    /**
     * @var ContaoFramework
     */
    protected $framework;


    /**
     * ContentListener constructor.
     */
    public function __construct(ContaoFramework $framework)
    {
        $this->framework = $framework;
    }

    public function getTeaserLinkText()
    {
        $arrOptions = [];

        $arrTitles = $GLOBALS['TL_LANG']['MSC']['linkteaser']['teaserlinktext'];

        if (!is_array($arrTitles))
        {
            return $arrOptions;
        }

        foreach ($arrTitles as $strKey => $strTitle)
        {
            if (is_array($strTitle))
            {
                $strTitle = $strTitle[0];
            }

            $arrOptions[$strKey] = $strTitle;
        }

        return $arrOptions;
    }

    public function onLoad(DataContainer $dc)
    {
        $contentModel = ContentModel::findByPk($dc->id);
        if (!$contentModel->type === LinkTeaserElement::TYPE) {
            return;
        }

        // update core fields
        $dca = &$GLOBALS['TL_DCA']['tl_content'];
        $dca['fields']['text']['eval']['mandatory'] = false;
        $dca['fields']['target']['load_callback'][] = [__CLASS__, 'setTargetFlags'];
        $dca['fields']['article']['label'] = &$GLOBALS['TL_LANG']['tl_content']['articleId'];
        $dca['fields']['article']['eval']['submitOnChange'] = false;
    }

    /**
     * Dynamically add flags to the "target" field
     *
     * @param mixed $varValue
     * @param DataContainer $dc
     *
     * @return mixed
     */
    public function setTargetFlags($varValue, DataContainer $dc)
    {
        if ($dc->activeRecord)
        {
            switch ($dc->activeRecord->source)
            {
                case 'file':
                    $GLOBALS['TL_DCA'][$dc->table]['fields'][$dc->field]['default'] = true;
                    break;
            }
        }

        return $varValue;
    }

    /**
     * Dynamically add flags to the "fileSRC" field
     *
     * @param mixed $varValue
     * @param  DataContainer $dc
     *
     * @return mixed
     */
    public function setFileSrcFlags($varValue, DataContainer $dc)
    {
        if ($dc->activeRecord)
        {
            switch ($dc->activeRecord->source)
            {
                case 'download':
                    $GLOBALS['TL_DCA'][$dc->table]['fields'][$dc->field]['eval']['extensions'] = Config::get('allowedDownload');
                    break;
            }
        }

        return $varValue;
    }

    /**
     * Add the source options depending on the allowed fields (see #5498)
     *
     * @param  DataContainer $dc
     *
     * @return array
     */
    public function getSourceOptions(DataContainer $dc)
    {
        $user     = $this->framework->createInstance(BackendUser::class);

        if ($user->isAdmin)
        {
            $arrOptions = [
                LinkTeaserElement::SOURCE_PAGE,
                'file',
                'download',
                LinkTeaserElement::SOURCE_ARTICLE,
                'external'
            ];

            // HOOK: extend options by callback functions
            if (isset($GLOBALS['TL_HOOKS']['getContentSourceOptions']) && is_array($GLOBALS['TL_HOOKS']['getContentSourceOptions']))
            {
                foreach ($GLOBALS['TL_HOOKS']['getContentSourceOptions'] as $callback)
                {
                    $arrOptions = System::importStatic($callback[0])->{$callback[1]}($arrOptions, $dc);
                }
            }

            return $arrOptions;
        }

        $arrOptions = array();

        // Add the "file" and "download" option
        if ($user->hasAccess('tl_content::fileSRC', 'alexf'))
        {
            $arrOptions[] = 'file';
            $arrOptions[] = 'download';
        }

        // Add the "page" option
        if ($user->hasAccess('tl_content::jumpTo', 'alexf'))
        {
            $arrOptions[] = 'page';
        }

        // Add the "article" option
        if ($user->hasAccess('tl_content::article', 'alexf'))
        {
            $arrOptions[] = 'article';
        }

        // Add the "external" option
        if ($user->hasAccess('tl_content::url', 'alexf'))
        {
            $arrOptions[] = 'external';
        }

        // HOOK: extend options by callback functions
        if (isset($GLOBALS['TL_HOOKS']['getContentSourceOptions']) && is_array($GLOBALS['TL_HOOKS']['getContentSourceOptions']))
        {
            foreach ($GLOBALS['TL_HOOKS']['getContentSourceOptions'] as $callback)
            {
                $arrOptions = System::importStatic($callback[0])->{$callback[1]}($arrOptions, $dc);
            }
        }

        // Add the option currently set
        if ($dc->activeRecord && $dc->activeRecord->source != '')
        {
            $arrOptions[] = $dc->activeRecord->source;
            $arrOptions = array_unique($arrOptions);
        }

        return $arrOptions;
    }

    /**
     * Return all teaser content templates as array
     *
     * @return array
     */
    public function getTeaserContentTemplates()
    {
        return Backend::getTemplateGroup('linkteaser_content_');
    }
}
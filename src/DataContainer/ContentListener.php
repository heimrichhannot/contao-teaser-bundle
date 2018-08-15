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
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\Database;
use Contao\DataContainer;
use Contao\Input;
use Contao\System;
use HeimrichHannot\ContaoTeaserBundle\ContentElement\LinkTeaserElement;

class ContentListener
{
    /**
     * @var ContaoFrameworkInterface
     */
    private $framework;

    public function __construct(ContaoFrameworkInterface $framework)
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

    /**
     * Modify the current datacontainer palette
     *
     * @param DataContainer $dc
     */
    public function modifyPalette(DataContainer $dc)
    {
        $id = strlen(Input::get('id')) ? Input::get('id') : CURRENT_ID;

        $objModel = ContentModel::findByPk($id);

        if (!$objModel || $objModel->type != LinkTeaserElement::TYPE)
        {
            return;
        }

        $dca = &$GLOBALS['TL_DCA']['tl_content'];

        // make text non mandatory
        $dca['fields']['text']['eval']['mandatory'] = false;

        $dca['fields']['target']['load_callback'][] = [__CLASS__, 'setTargetFlags'];
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
     * Get all articles and return them as array
     *
     * @param  DataContainer $dc
     *
     * @return array
     */
    public function getArticleAlias(DataContainer $dc)
    {
        $database = $this->framework->createInstance(Database::class);
        $user     = $this->framework->createInstance(BackendUser::class);
        $pids  = [];
        $aliases = [];

        if (!$user->isAdmin)
        {
            foreach ($user->pagemounts as $id)
            {
                $pids[] = $id;
                $pids   = array_merge($pids, $database->getChildRecords($id, 'tl_page'));
            }

            if (empty($pids))
            {
                return $aliases;
            }

            $objAlias = $database->prepare("SELECT a.id, a.title, a.inColumn, p.title AS parent FROM tl_article a LEFT JOIN tl_page p ON p.id=a.pid WHERE a.pid IN(" . implode(',', array_map('intval', array_unique($pids))) . ") ORDER BY parent, a.sorting")
                ->execute($dc->id);
        } else
        {
            $objAlias = $database->prepare("SELECT a.id, a.title, a.inColumn, p.title AS parent FROM tl_article a LEFT JOIN tl_page p ON p.id=a.pid ORDER BY parent, a.sorting")
                ->execute($dc->id);
        }

        if ($objAlias->numRows)
        {
            System::loadLanguageFile('tl_article');

            while ($objAlias->next())
            {
                $aliases[$objAlias->parent][$objAlias->id] = $objAlias->title . ' (' . ($GLOBALS['TL_LANG']['COLS'][$objAlias->inColumn] ?: $objAlias->inColumn) . ', ID ' . $objAlias->id . ')';
            }
        }

        return $aliases;
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
            $arrOptions = array('page', 'file', 'download', 'article', 'external');

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
        if ($user->hasAccess('tl_content::articleId', 'alexf'))
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
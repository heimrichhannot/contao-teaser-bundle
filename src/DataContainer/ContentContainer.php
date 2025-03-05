<?php

/**
 * Contao Open Source CMS.
 *
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @author  Thomas Körner <t.koerner@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\ContaoTeaserBundle\DataContainer;

use Contao\Backend;
use Contao\Config;
use Contao\ContentModel;
use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\DataContainer;
use Contao\System;
use HeimrichHannot\ContaoTeaserBundle\ContentElement\LinkTeaserElement;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;

class ContentContainer
{
    public const LINK_TEXT_CUSTOM = 'custom';

    public function __construct(
        private readonly Security $security,
        private readonly RequestStack $requestStack,
    ) {
    }

    /**
     * @Callback(table="tl_content", target="fields.teaserLinkText.options")
     *
     * @noinspection PhpUnused
     */
    public function getTeaserLinkText(): array
    {
        $arrOptions = [
            static::LINK_TEXT_CUSTOM => $GLOBALS['TL_LANG']['MSC']['linkteaser']['customLinkText'],
        ];

        $arrTitles = $GLOBALS['TL_LANG']['MSC']['linkteaser']['teaserlinktext'];

        if (!is_array($arrTitles)) {
            return $arrOptions;
        }

        $options = [
            $GLOBALS['TL_LANG']['MSC']['linkteaser']['customLinkText'] => $arrOptions,
        ];

        $arrOptions = [];

        foreach ($arrTitles as $strKey => $strTitle) {
            if (is_array($strTitle)) {
                $strTitle = $strTitle[0];
            }

            $arrOptions[$strKey] = $strTitle;
        }

        $options[$GLOBALS['TL_LANG']['MSC']['linkteaser']['predefinedLinkText']] = $arrOptions;

        return $options;
    }

    /**
     * @Callback(table="tl_content", target="config.onload")
     */
    public function onLoadCallback(?DataContainer $dc = null): void
    {
        if (!$dc || !$dc->id || 'edit' !== $this->requestStack->getCurrentRequest()->query->get('act')) {
            return;
        }

        $contentModel = ContentModel::findByPk($dc->id);
        if (!$contentModel || LinkTeaserElement::TYPE !== $contentModel->type) {
            return;
        }

        // update core fields
        $dca = &$GLOBALS['TL_DCA']['tl_content'];
        $dca['fields']['text']['eval']['mandatory'] = false;
        $dca['fields']['target']['load_callback'][] = [self::class, 'setTargetFlags'];
        $dca['fields']['article']['label'] = &$GLOBALS['TL_LANG']['tl_content']['articleId'];
        $dca['fields']['article']['eval']['submitOnChange'] = false;
        $dca['fields']['linkTitle']['label'][1] = $GLOBALS['TL_LANG']['tl_content']['linkTitle']['huh_teaser'] ?? $dca['fields']['linkTitle']['label'][1];
        $dca['fields']['titleText']['label'][1] = $GLOBALS['TL_LANG']['tl_content']['titleText']['huh_teaser'] ?? $dca['fields']['titleText']['label'][1];
        $dca['fields']['linkTitle']['eval']['tl_class'] = 'w50 clr';
        $dca['fields']['linkTitle']['eval']['mandatory'] = true;

        if (static::LINK_TEXT_CUSTOM === $contentModel->teaserLinkText) {
            PaletteManipulator::create()
                ->addField('linkTitle', 'teaserLinkText')
                ->addField('teaserAriaLabel', 'linkTitle')
                ->addField('titleText', 'teaserAriaLabel')
                ->applyToPalette(LinkTeaserElement::TYPE, ContentModel::getTable());
        }

        // do the palette handling manually because of issues in contao dca palette calculation
        if ($contentModel->source) {
            $dca['palettes'][LinkTeaserElement::TYPE] = \str_replace(
                ',source,',
                ',source,' . $dca['subpalettes']['source_' . $contentModel->source] . ',',
                $dca['palettes'][LinkTeaserElement::TYPE]
            );
        } else {
            $dca['palettes'][LinkTeaserElement::TYPE] = \str_replace(
                ',source,',
                ',source,' . $dca['subpalettes']['source_page'] . ',',
                $dca['palettes'][LinkTeaserElement::TYPE]
            );
        }
    }

    /**
     * Dynamically add flags to the "target" field.
     */
    public function setTargetFlags(mixed $varValue, DataContainer $dc)
    {
        if ($dc->activeRecord) {
            switch ($dc->activeRecord->source) {
                case 'file':
                    $GLOBALS['TL_DCA'][$dc->table]['fields'][$dc->field]['default'] = true;
                    break;
            }
        }

        return $varValue;
    }

    /**
     * Dynamically add flags to the "fileSRC" field.
     *
     * @Callback(table="tl_content", target="fields.fileSRC.load")
     *
     * @template T
     *
     * @param T             $value
     * @param DataContainer $dc
     *
     * @return T
     */
    public function setFileSrcFlags($value, $dc)
    {
        if ($dc->activeRecord) {
            switch ($dc->activeRecord->source) {
                case 'download':
                    $GLOBALS['TL_DCA'][$dc->table]['fields'][$dc->field]['eval']['extensions'] = Config::get('allowedDownload');
                    break;
            }
        }

        return $value;
    }

    /**
     * Add the source options depending on the allowed fields (see #5498).
     *
     * @Callback(table="tl_content", target="fields.source.options")
     */
    public function onSourceOptionsCallback(?DataContainer $dc = null): array
    {
        $user = $this->security->getUser();

        if ($user->isAdmin) {
            $arrOptions = [
                LinkTeaserElement::SOURCE_PAGE,
                LinkTeaserElement::SOURCE_FILE,
                LinkTeaserElement::SOURCE_DOWNLOAD,
                LinkTeaserElement::SOURCE_ARTICLE,
                LinkTeaserElement::SOURCE_EXTERNAL,
            ];

            // HOOK: extend options by callback functions
            if (isset($GLOBALS['TL_HOOKS']['getContentSourceOptions']) && \is_array($GLOBALS['TL_HOOKS']['getContentSourceOptions'])) {
                foreach ($GLOBALS['TL_HOOKS']['getContentSourceOptions'] as $callback) {
                    $arrOptions = System::importStatic($callback[0])->{$callback[1]}($arrOptions, $dc);
                }
            }

            return $arrOptions;
        }

        $arrOptions = [];

        // Add the "file" and "download" option
        if ($user->hasAccess('tl_content::fileSRC', 'alexf')) {
            $arrOptions[] = 'file';
            $arrOptions[] = 'download';
        }

        // Add the "page" option
        if ($user->hasAccess('tl_content::jumpTo', 'alexf')) {
            $arrOptions[] = 'page';
        }

        // Add the "article" option
        if ($user->hasAccess('tl_content::article', 'alexf')) {
            $arrOptions[] = 'article';
        }

        // Add the "external" option
        if ($user->hasAccess('tl_content::url', 'alexf')) {
            $arrOptions[] = 'external';
        }

        // HOOK: extend options by callback functions
        if (isset($GLOBALS['TL_HOOKS']['getContentSourceOptions']) && \is_array($GLOBALS['TL_HOOKS']['getContentSourceOptions'])) {
            foreach ($GLOBALS['TL_HOOKS']['getContentSourceOptions'] as $callback) {
                $arrOptions = System::importStatic($callback[0])->{$callback[1]}($arrOptions, $dc);
            }
        }

        // Add the option currently set
        if ($dc->activeRecord && '' != $dc->activeRecord->source) {
            $arrOptions[] = $dc->activeRecord->source;
            $arrOptions = \array_unique($arrOptions);
        }

        return $arrOptions;
    }

    /**
     * Return all teaser content templates as array.
     *
     * @Callback(table="tl_content", target="fields.teaserContentTemplate.options")
     */
    public function getTeaserContentTemplates(): array
    {
        return Backend::getTemplateGroup('linkteaser_content_');
    }
}

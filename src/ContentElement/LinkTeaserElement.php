<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2016 Heimrich & Hannot GmbH
 *
 * @package ${CARET}
 * @author  Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\ContaoTeaserBundle\ContentElement;

use Contao\ArticleModel;
use Contao\BackendTemplate;
use Contao\Config;
use Contao\ContentModel;
use Contao\ContentText;
use Contao\Controller;
use Contao\Environment;
use Contao\File;
use Contao\FrontendTemplate;
use Contao\Input;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\System;
use Contao\Template;
use HeimrichHannot\ContaoTeaserBundle\DataContainer\ContentContainer;
use HeimrichHannot\UtilsBundle\Util\Utils;

class LinkTeaserElement extends ContentText
{
    public const TYPE = 'linkteaser';

    public const SOURCE_PAGE = 'page';
    public const SOURCE_FILE = 'file';
    public const SOURCE_DOWNLOAD = 'download';
    public const SOURCE_ARTICLE = 'article';
    public const SOURCE_EXTERNAL = 'external';

    public const LINK_BEHAVIOUR_SHOW_LINK = 'default';
    public const LINK_BEHAVIOUR_LINK_ALL = 'linkAll';
    public const LINK_BEHAVIOUR_HIDE_LINK = 'hideLink';

    /**
     * Template
     *
     * @var string
     */
    protected $strTemplate = 'ce_linkteaser';

    protected $showMore = false;

    protected $strHref;

    protected $strTitle;

    protected $strLink;

    protected $blnActive;

    protected $blnTrail;

    protected $label;

    protected $linkTemplate = 'linkteaser_link_default';

    protected $arrLinkAttributes = [];

    public const LINK_CSS_CLASS = 'more';

    /** Titel der Entität für den Link */
    private string $targetTitle = '';

    protected function compile()
    {
        parent::compile();

        $this->generateLink();
    }

    /**
     * Generate the teaser Link
     */
    protected function generateLink()
    {
        global $objPage;

        if (ContentContainer::LINK_TEXT_CUSTOM !== $this->teaserLinkText) {
            $this->label = ($GLOBALS['TL_LANG']['MSC']['linkteaser']['teaserlinktext'][$this->teaserLinkText] ?? '');
        }
        $this->setLink(is_array($this->label) ? $this->label[0] : $this->label);

        $this->showMore = match ($this->source) {
            'page' => $this->handlePage(),
            'file' => $this->handleFile(),
            'download' => $this->handleDownload(),
            'article' => $this->handleArticle(),
            'external' => $this->handleExternal(),
            default => false,
        };

        // HOOK: extend teaser link by callback functions
        if (isset($GLOBALS['TL_HOOKS']['generateTeaserLink']) && is_array($GLOBALS['TL_HOOKS']['generateTeaserLink'])) {
            foreach ($GLOBALS['TL_HOOKS']['generateTeaserLink'] as $callback) {
                $showMore = System::importStatic($callback[0])->{$callback[1]}($this, $this->showMore);
            }
            $this->showMore = $showMore ?? null;
        }

        if (!$this->showMore) {
            if (System::getContainer()->get(Utils::class)->container()->isDev()) {
                $this->content = '<!-- Teaser Bundle: Source was not found or hook returned false (showMore = false) -->';
            }
            return;
        }

        switch ($this->teaserLinkBehaviour) {
            case 'linkAll':
                $this->Template->linkAll = true;
                $this->Template->showMore = true;
                break;
            case 'hideLink':
                $this->Template->linkAll = true;
                $this->Template->showMore = false;
                break;
            default:
                $this->Template->linkAll = false;
                $this->Template->showMore = true;
        }

        $this->Template->href = $this->getHref();
        $this->Template->linkClass = static::LINK_CSS_CLASS . ($this->teaserLinkCssClass ? ' ' . $this->teaserLinkCssClass : '');

        if ($objPage && $this->target) {
            $this->Template->target = (($objPage->outputFormat == 'xhtml') ? ' onclick="return !window.open(this.href)"' : ' target="_blank"');
            $this->addLinkAttribute('rel', 'noopener');
        }

        $this->addLinkToTemplate($this->Template, $this->getModel());

        $this->Template->linkTemplate = $this->getLinkTemplate();
        $this->Template->content = $this->generateContent();

        $this->addContainerClass($this->addImage ? 'has-image' : 'no-image');
    }

    private function addLinkToTemplate(Template $template, ContentModel $model): void
    {
        if (ContentContainer::LINK_TEXT_CUSTOM === $model->teaserLinkText) {

            $template->link = str_replace('%title%', $this->targetTitle, $model->linkTitle);

            if ($model->teaserAriaLabel) {
                $template->ariaLabel = str_replace(
                    ['%title%', '%link%'],
                    [$this->targetTitle, $template->link],
                    $model->teaserAriaLabel
                );
            } else {
                $template->ariaLabel = $this->getTitle();
            }
            if ($model->titleText) {
                $template->linkTitle = str_replace(
                    ['%title%', '%link%'],
                    [$this->targetTitle, $template->link],
                    $model->titleText
                );
            } else {
                $template->linkTitle = $this->getTitle();
            }
        } else {
            $this->Template->link = $this->getLink();
            $this->Template->ariaLabel = $this->getTitle();
            $this->Template->linkTitle = $this->getTitle();
        }

        $this->Template->linkAttributes = !empty($this->getLinkAttributes()) ? ' ' . $this->getLinkAttributes(true) : '';
    }

    /**
     * Generate the teaser content
     *
     * @return string The parsed teaser content
     */
    protected function generateContent()
    {
        if (System::getContainer()->get(Utils::class)->container()->isBackend()) {
            Controller::loadDataContainer('tl_content');
            $template = new BackendTemplate('be_wildcard');
            $template->title = $this->headline;
            $wildcard = $this->Template->linkTitle;
            $template->wildcard = $wildcard;
            return $template->parse();
        }
        switch ($this->floating) {
            case 'left':
                $strTemplate = 'linkteaser_content_image_left';
                $this->addContainerClass('float_left');
                break;
            case 'right':
                $strTemplate = 'linkteaser_content_image_right';
                $this->addContainerClass('float_right');
                break;
            case 'below':
                $strTemplate = 'linkteaser_content_image_below';
                $this->addContainerClass('float_below');
                break;
            default:
                $strTemplate = 'linkteaser_content_image_above';
                $this->addContainerClass('float_above');
        }

        // overwrite content template
        if ($this->teaserContentTemplate != '') {
            $strTemplate = $this->teaserContentTemplate;
        }

        $objT = new FrontendTemplate($strTemplate);
        $objT->setData($this->Template->getData());
        // background images dont have width/height in backend view
        $objT->background = System::getContainer()->get(Utils::class)->container()->isBackend()
            ? false
            : $objT->background;

        if($this->isActive()) {
            $this->addContainerClass('active');
        }
        else if($this->isTrail()) {
            $this->addContainerClass('trail');
        }

        return $objT->parse();
    }

    /**
     * Handle page links
     *
     * @return bool return true, or false if the page does not exist
     */
    protected function handlePage()
    {
        global $objPage;

        $objTarget = PageModel::findPublishedById($this->jumpTo);

        if ($objTarget === null) {
            return false;
        }

        $objTarget = $objTarget->loadDetails();

        if ($objTarget->target || ($objTarget->domain != '' && $objTarget->domain != Environment::get('host'))) {
            $this->target = true;
        }

        $this->setHref($objTarget->getAbsoluteUrl());

        // remove alias from root pages
        if ($objTarget->type == 'root') {
            $this->setHref(str_replace($objTarget->alias, '', $this->getHref()));
        }

        $this->setTitle(sprintf($GLOBALS['TL_LANG']['MSC']['linkteaser']['pageTitle'], $objTarget->pageTitle ?: $objTarget->title));
        $this->setLink(sprintf($this->getLink(), $objTarget->title));
        $this->targetTitle = $objTarget->pageTitle ?: $objTarget->title;

        $utils = System::getContainer()->get(Utils::class);

        if($utils->container()->isFrontend() && $objPage !== null) {
            if ($objPage->id == $objTarget->id) {
                $this->setActive(true);
            }
            else if(is_array($objPage->trail) && in_array($objTarget->id, $objPage->trail)) {
                $this->setTrail(true);
            }
        }

        return true;
    }

    /**
     * Handle files
     *
     * @return bool return true, or false if the file does not exist
     */
    protected function handleFile()
    {
        $utils = System::getContainer()->get(Utils::class);
        $objFile = new File($utils->file()->getPathFromUuid($this->fileSRC));

        if ($objFile === null) {
            return false;
        }

        $arrMeta = $this->getMetaFromFile($objFile);

        $this->setHref($objFile->path);
        $this->setTitle(sprintf($GLOBALS['TL_LANG']['MSC']['linkteaser']['fileTitle'], $arrMeta['title']));
        $this->setLink(sprintf($this->getLink(), $arrMeta['title']));
        $this->targetTitle = $arrMeta['title'];

        return true;
    }

    /**
     * Handle downloads
     *
     * @return bool return true, or false if the file does not exist
     */
    protected function handleDownload()
    {
        $utils = System::getContainer()->get(Utils::class);
        $objFile = new File($utils->file()->getPathFromUuid($this->fileSRC));

        if ($objFile === null) {
            return false;
        }

        $allowedDownload = StringUtil::trimsplit(',', strtolower(Config::get('allowedDownload')));

        // Return if the file type is not allowed
        if (!in_array($objFile->extension, $allowedDownload) || preg_match('/^meta(_[a-z]{2})?\.txt$/', $objFile->basename)) {
            return false;
        }

        $arrMeta = $this->getMetaFromFile($objFile);

        $file = Input::get('file', true);

        // Send the file to the browser and do not send a 404 header (see #4632)
        if ($file != '' && $file == $objFile->path) {
            Controller::sendFileToBrowser($file);
        }

        $this->setHref(Environment::get('request'));

        // Remove an existing file parameter (see #5683)
        if (preg_match('/(&(amp;)?|\?)file=/', (string) $this->getHref())) {
            $this->setHref(preg_replace('/(&(amp;)?|\?)file=[^&]+/', '', $this->getHref()));
        }

        $this->setHref(sprintf(
            '%s%sfile=%s',
            $this->getHref(),
            ((Config::get('disableAlias') || str_contains((string) $this->getHref(), '?')) ? '&amp;' : '?'),
            System::urlEncode($objFile->path)
        ));
        $this->setTitle(sprintf($GLOBALS['TL_LANG']['MSC']['linkteaser']['downloadTitle'], $arrMeta['title']));
        $this->setLink(sprintf($this->getLink(), $arrMeta['title']));
        $this->targetTitle = $arrMeta['title'];

        return true;
    }

    /**
     * Handle articles
     *
     * @return bool return true, or false if the articles does not exist
     */
    protected function handleArticle()
    {
        if (($objArticle = ArticleModel::findPublishedById($this->article, [
            'eager' => true,
        ])) === null) {
            return false;
        }

        if (($objTarget = PageModel::findPublishedById($objArticle->pid)) === null) {
            return false;
        }

        $objTarget = $objTarget->loadDetails();

        if ($objTarget->domain != '' && $objTarget->domain != Environment::get('host')) {
            $this->target = true;
        }

        $strParams = '/articles/' . ((!Config::get('disableAlias') && $objArticle->alias != '') ? $objArticle->alias : $objArticle->id);

        $rootPage = PageModel::findByPk($objTarget->rootId);

        $this->setHref(StringUtil::ampersand($rootPage->getFrontendUrl($strParams)));
        $this->setTitle(sprintf($GLOBALS['TL_LANG']['MSC']['linkteaser']['articleTitle'], $objArticle->title));
        $this->setLink(sprintf($this->getLink(), $objArticle->title));
        $this->targetTitle = $objArticle->title;

        return true;
    }

    /**
     * Handle external urls
     *
     * @return bool return true, or false if the url does not exist
     */
    protected function handleExternal()
    {
        if ($this->url == '') {
            return false;
        }

        if (str_starts_with($this->url, 'mailto:')) {
            $this->setHref(StringUtil::encodeEmail($this->url));
            $this->setTitle(sprintf($GLOBALS['TL_LANG']['MSC']['linkteaser']['externalMailTitle'], $this->getHref()));
            $this->setLink(sprintf($this->getLink(), $this->getHref()));
            $this->targetTitle = $this->getHref();
        }
        else {
            $this->setHref(StringUtil::ampersand($this->url));
            $strLinkTitle = $this->getLinkTitle($this->getHref());
            $this->setTitle(sprintf($GLOBALS['TL_LANG']['MSC']['linkteaser']['externalLinkTitle'], $strLinkTitle));
            $this->setLink(sprintf($this->getLink(), $strLinkTitle));
            $this->targetTitle = $strLinkTitle;
        }

        return true;
    }

    /**
     * Generate the meta information for a given file
     *
     * @return array The meta information with i18n support
     */
    protected function getMetaFromFile(File $objFile): array
    {
        global $objPage;

        $objModel = $objFile->getModel();

        $this->getCurrentMetaData($objModel->meta ?? '', $objPage);

        // Use the file name as title if none is given
        if (empty($arrMeta['title'])) {
            $arrMeta['title'] = StringUtil::specialchars($objFile->basename);
        }

        return $arrMeta;
    }

    /**
     * Convert {{*_url::*}} inserttags to its entity title
     *
     * @return string The link title of the element (page, article, news, event, faq)
     */
    protected function getLinkTitle($strHref)
    {
        // Replace inserttag links with title
        if (!str_contains((string) $strHref, '{{') || !str_contains((string) $strHref, '}}')) {
            return $strHref;
        }

        $arrTag = StringUtil::trimsplit('::', str_replace(['{{', '}}'], '', $strHref));

        if (empty($arrTag) || $arrTag[0] == '' || $arrTag[1] == '') {
            return $strHref;
        }

        switch ($arrTag[0]) {
            case 'link_url':
                return sprintf('{{link_title::%d}}', $arrTag[1]);
            case 'article_url':
                return sprintf('{{article_title::%d}}', $arrTag[1]);
            case 'news_url':
                return sprintf('{{news_title::%d}}', $arrTag[1]);
            case 'event_url':
                return sprintf('{{event_title::%d}}', $arrTag[1]);
            case 'faq_url':
                return sprintf('{{faq_title::%d}}', $arrTag[1]);
        }
    }

    protected function addContainerClass($strClass)
    {
        $this->arrData['cssID'][1] .= ' ' . $strClass;
    }

    public function setHref($varValue): void
    {
        $this->strHref = $varValue;
    }

    public function getHref()
    {
        return $this->strHref;
    }

    public function setTitle($varValue): void
    {
        $this->strTitle = $varValue;
    }

    public function getTitle()
    {
        return $this->strTitle;
    }

    public function setLink($varValue): void
    {
        $this->strLink = $varValue;
    }

    public function getLink()
    {
        return $this->strLink;
    }

    public function setLinkTemplate($varValue): void
    {
        $this->linkTemplate = $varValue;
    }

    public function getLinkTemplate()
    {
        return $this->linkTemplate;
    }

    /**
     * @return mixed
     */
    public function isActive()
    {
        return $this->blnActive;
    }

    public function setActive(mixed $blnActive): void
    {
        $this->blnActive = $blnActive;
    }

    /**
     * @return mixed
     */
    public function isTrail()
    {
        return $this->blnTrail;
    }

    public function setTrail(mixed $blnTrail): void
    {
        $this->blnTrail = $blnTrail;
    }

    public function setLinkAttributes($arrData, $delimiter = " "): void
    {
        // set from string
        if (!is_array($arrData)) {
            $arrData = StringUtil::trimsplit($delimiter, $arrData);

            if (is_array($arrData)) {
                foreach (array_keys($this->arrLinkAttributes) as $strKey) {
                    $this->arrLinkAttributes[$strKey] = $arrData[$strKey];
                }
            }

            return;
        }

        $this->arrLinkAttributes = $arrData;
    }

    public function getLinkAttributes($blnReturnString = false)
    {
        if (!$blnReturnString) {
            return $this->arrLinkAttributes;
        }

        $strAttributes = '';

        foreach (array_keys($this->arrLinkAttributes) as $strKey) {
            $strAttributes .= sprintf('%s="%s"', $strKey, $this->arrLinkAttributes[$strKey]);
        }

        return $strAttributes;
    }

    public function addLinkAttribute($key, $value): void
    {
        $this->arrLinkAttributes[$key] = $value;
    }

    public function removeLinkAttribute($key): void
    {
        unset($this->arrLinkAttributes);
    }

    /**
     * @param $objFile
     * @param $objModel
     * @param $objPage
     * @return void
     */
    private function getCurrentMetaData(string $meta, PageModel $pageModel = null): array
    {
        if (System::getContainer()->get(Utils::class)->container()->isBackend()) {
            $arrMeta = $this->getMetaData($meta, $GLOBALS['TL_LANGUAGE']);
        } else {
            $arrMeta = $this->getMetaData($meta, $pageModel?->language);

            if (empty($arrMeta) && $pageModel->rootFallbackLanguage !== null) {
                $arrMeta = $this->getMetaData($meta, $pageModel->rootFallbackLanguage);
            }
        }

        return $arrMeta;
    }
}

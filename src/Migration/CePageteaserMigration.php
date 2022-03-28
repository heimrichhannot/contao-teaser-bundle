<?php

namespace HeimrichHannot\ContaoTeaserBundle\Migration;

use Contao\ContentModel;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Migration\MigrationInterface;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Exception\InvalidFieldNameException;
use HeimrichHannot\ContaoTeaserBundle\ContentElement\LinkTeaserElement;

class CePageteaserMigration implements MigrationInterface
{
    private ContaoFramework $contaoFramework;

    public function __construct(ContaoFramework $contaoFramework)
    {
        $this->contaoFramework = $contaoFramework;
    }

    public function getName(): string
    {
        return "ce_page_teaser to Teaser Bundle Migration";
    }

    public function shouldRun(): bool
    {
        $this->contaoFramework->initialize();
        try {
            if (ContentModel::findByType('page_teaser')) {
                return true;
            }
            if (ContentModel::findByType('teaser')) {
                return true;
            }
        } catch (InvalidFieldNameException $exception) {
            return false;
        }


        return false;
    }

    public function run(): MigrationResult
    {
        $pageTeaser = ContentModel::findByType('page_teaser');
        $articleTeaser = ContentModel::findByType('teaser');

        $pageTeaserCount = 0;
        $articleTeaserCount = 0;

        if ($pageTeaser) {
            foreach ($pageTeaser as $teaser)
            {
                $teaser->type = LinkTeaserElement::TYPE;
                $teaser->source = LinkTeaserElement::SOURCE_PAGE;
                $teaser->jumpTo = $teaser->page_teaser_page;
                if (!$teaser->showMore) {
                    $teaser->teaserLinkBehaviour = LinkTeaserElement::LINK_BEHAVIOUR_HIDE_LINK;
                } else {
                    $teaser->teaserLinkBehaviour = LinkTeaserElement::LINK_BEHAVIOUR_SHOW_LINK;
                }
                $teaser->save();
                $pageTeaserCount++;
            }
        }

        if ($articleTeaser)
        {
            foreach ($articleTeaser as $teaser)
            {
                if (!$teaser->teaser_page_link && !$teaser->teaser_fragment_identifier)
                {
                    continue;
                }
                $teaser->type   = LinkTeaserElement::TYPE;
                $teaser->source = LinkTeaserElement::SOURCE_ARTICLE;
                $teaser->save();
                $articleTeaserCount++;
            }
        }

        return new MigrationResult(true, "ce_page_teaser Migration successfull! Migrated $pageTeaserCount page_teaser and $articleTeaserCount teaser elements.");
    }
}
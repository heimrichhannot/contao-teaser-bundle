<?php

namespace HeimrichHannot\ContaoTeaserBundle\Migration;

use Contao\ContentModel;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Migration\MigrationInterface;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\InvalidFieldNameException;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use HeimrichHannot\ContaoTeaserBundle\ContentElement\LinkTeaserElement;

class CePageteaserMigration implements MigrationInterface
{
    private readonly ContaoFramework $contaoFramework;

    private readonly Connection $connection;

    public function __construct(ContaoFramework $contaoFramework, Connection $connection)
    {
        $this->contaoFramework = $contaoFramework;
        $this->connection = $connection;
    }

    public function getName(): string
    {
        return "ce_page_teaser to Teaser Bundle Migration";
    }

    public function shouldRun(): bool
    {
        $schemaManager = $this->getSchemaManager();

        if (!$schemaManager->tablesExist(ContentModel::getTable())) {
            return false;
        }
        if (!in_array(strtolower('teaserLinkBehaviour'), array_keys($schemaManager->listTableColumns(ContentModel::getTable())))) {
            return false;
        }

        $columns = array_keys($schemaManager->listTableColumns(ContentModel::getTable()));

        $this->contaoFramework->initialize();
        $t = ContentModel::getTable();

        if (in_array('teaser_page_link', $columns)) {
            try {
                if (ContentModel::findBy(["$t.type=?", "$t.teaser_page_link=?"], ['teaser', '1'])) {
                    return true;
                }
            } catch (InvalidFieldNameException) {
            }
        }
        if (in_array('teaser_fragment_identifier', $columns)) {
            try {
                if (ContentModel::findBy(["$t.type=?", "$t.teaser_fragment_identifier=?"], ['teaser', '1'])) {
                    return true;
                }
            } catch (InvalidFieldNameException) {
            }
        }

        try {
            if (ContentModel::findByType('page_teaser')) {
                return true;
            }
        } catch (InvalidFieldNameException) {
        }

        return false;
    }

    public function run(): MigrationResult
    {
        $schemaManager = $this->getSchemaManager();
        if (!in_array(strtolower('teaserLinkBehaviour'), array_keys($schemaManager->listTableColumns(ContentModel::getTable())))) {
            return new MigrationResult(false, "Ce Page Teaser Migration needs schema update before running.");
        }

        $pageTeaser = ContentModel::findByType('page_teaser');
        $articleTeaser = ContentModel::findByType('teaser');

        $pageTeaserCount = 0;
        $articleTeaserCount = 0;

        if ($pageTeaser) {
            foreach ($pageTeaser as $teaser) {
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

        if ($articleTeaser) {
            foreach ($articleTeaser as $teaser) {
                if (!$teaser->teaser_page_link && !$teaser->teaser_fragment_identifier) {
                    continue;
                }
                $teaser->type = LinkTeaserElement::TYPE;
                $teaser->source = LinkTeaserElement::SOURCE_ARTICLE;
                $teaser->save();
                $articleTeaserCount++;
            }
        }

        return new MigrationResult(
            true,
            "ce_page_teaser Migration successfull! Migrated $pageTeaserCount page_teaser and $articleTeaserCount teaser elements."
        );
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    protected function getSchemaManager(): AbstractSchemaManager
    {
        if (method_exists($this->connection, 'createSchemaManager')) {
            $schemaManager = $this->connection->createSchemaManager();
        } else {
            /** @noinspection PhpDeprecationInspection */
            $schemaManager = $this->connection->getSchemaManager();
        }
        return $schemaManager;
    }
}
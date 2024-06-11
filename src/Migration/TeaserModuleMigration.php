<?php

namespace HeimrichHannot\ContaoTeaserBundle\Migration;

use Contao\ContentModel;
use Contao\CoreBundle\Migration\MigrationInterface;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\InvalidFieldNameException;
use HeimrichHannot\ContaoTeaserBundle\ContentElement\LinkTeaserElement;

class TeaserModuleMigration implements MigrationInterface
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function getName(): string
    {
        return 'Teaser Module to Teaser Bundle Migration';
    }

    public function shouldRun(): bool
    {
        $columns = $this->connection->createSchemaManager()->listTableColumns(ContentModel::getTable());
        if (!array_key_exists('articleid', $columns)) {
            return false;
        }

        try {
            $elements = ContentModel::findBy(
                [ContentModel::getTable().'.type=?', ContentModel::getTable().'.articleId!=0',],
                [LinkTeaserElement::TYPE,]
            );
        } catch (InvalidFieldNameException) {
            return false;
        }


        if (!$elements) {
            return false;
        }

        foreach ($elements as $element) {
            if ((int)$element->article === 0) {
                return true;
            }
        }

        return false;
    }

    public function run(): MigrationResult
    {
        $elements = ContentModel::findBy(
            [ContentModel::getTable().'.type=?', ContentModel::getTable().'.articleId!=0',],
            [LinkTeaserElement::TYPE,]
        );

        if (!$elements) {
            return new MigrationResult(false, "Found no teaser elements to migrate.");
        }

        $elementCount = 0;
        foreach ($elements as $element) {
            if ((int)$element->article === 0) {
                $element->article = $element->articleId;
                $element->articleId = 0;
                $element->save();
                $elementCount++;

            }
        }

        return new MigrationResult(true, "Teaser Module Migration successfull. Migrated $elementCount elements.");
    }
}
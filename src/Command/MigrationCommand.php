<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @author  Thomas Körner <t.koerner@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */


namespace HeimrichHannot\ContaoTeaserBundle\Command;


use Contao\ContentModel;
use Contao\CoreBundle\Command\AbstractLockedCommand;
use HeimrichHannot\ContaoTeaserBundle\ContentElement\LinkTeaserElement;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

class MigrationCommand extends AbstractLockedCommand
{
    /**
     * @var bool
     */
    protected $dryRun = false;

    protected $directMigration = false;

    /**
     * @var InputInterface
     */
    protected $input;

    protected function configure()
    {
        $this
            ->setName('huh:teaser:migrate')
            ->setDescription('Provide migration from ce_page_teaser module.')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, "Performs a run without writing to database.")
            ->addOption('migration', null, InputOption::VALUE_REQUIRED, 'Do given migration without interrupt. Can be used for automatic script or deployment processes. Options: contao-ce_page_teaser, contao-teaser')
        ;

    }

    /**
     * Executes the command.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|null
     */
    protected function executeLocked(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Contao Teaser Bundle migration');

        $this->input = $input;

        if ($input->hasOption('dry-run') && $input->getOption('dry-run'))
        {
            $this->dryRun = true;
            $io->note("Dry run enabled, no data will be changed.");
            $io->newLine();
        }

        $this->getContainer()->get('contao.framework')->initialize();

        if ($input->hasOption('migration') && $input->getOption('migration')) {
            $this->directMigration = true;
            $migration = $input->getOption('migration');
        }
        else {
            $io->text("Please select from where you want to upgrade. Select contao-teaser, if you upgrade from a bundle 0.x release to 1.x.");

            $question = new ChoiceQuestion(
                "Please select upgrade option:",
                [
                    "contao-ce_page_teaser",
                    "contao-teaser",
                    "cancel"
                ]
            );
            $migration = $io->askQuestion($question);
        }


        switch ($migration)
        {
            case 'contao-ce_page_teaser':
                return $this->migrateFromCePageTeaserModule($io);
            case 'contao-teaser':
                return $this->migrateFromModule($io);
            case 'cancel':
                $this->finishedWithoutChanges($io);
                return 0;
        }
        $io->error("Given migration ".$migration." is not valid.");
        return 1;
    }

    protected function finishedWithoutChanges(SymfonyStyle $io)
    {
        $io->success("Finished command without doing anything.");
    }

    protected function migrateFromCePageTeaserModule(SymfonyStyle $io)
    {
        $pageTeaser = ContentModel::findByType('page_teaser');
        $articleTeaser = ContentModel::findByType('teaser');

        $io->text("Found following entries in tl_content:");

        $io->table(['Content Type', 'Count'],[
            ['teaser', $articleTeaser ? $articleTeaser->count() : 0],
            ['page_teaser', $pageTeaser ? $pageTeaser->count() : 0]
        ]);

        if (!$this->directMigration) {
            if (!$io->confirm('Start migrating to linkteaser element?')) {
                $this->finishedWithoutChanges($io);
                return 0;
            }
        }


        if ($pageTeaser) {
            $io->text("Migration page_teaser element");
            $io->progressStart($pageTeaser->count());
            foreach ($pageTeaser as $teaser)
            {
                $io->progressAdvance();
                $teaser->type = LinkTeaserElement::TYPE;
                $teaser->source = LinkTeaserElement::SOURCE_PAGE;
                $teaser->jumpTo = $teaser->page_teaser_page;
                if (!$teaser->showMore) {
                    $teaser->teaserLinkBehaviour = LinkTeaserElement::LINK_BEHAVIOUR_HIDE_LINK;
                } else {
                    $teaser->teaserLinkBehaviour = LinkTeaserElement::LINK_BEHAVIOUR_SHOW_LINK;
                }
                if (!$this->dryRun) {
                    $teaser->save();
                }
            }
            $io->progressFinish();
        }

        if ($articleTeaser)
        {
            $io->text("Migration article teaser element");
            $io->progressStart($articleTeaser->count());
            $articleTeaser = ContentModel::findByType('teaser');
            foreach ($articleTeaser as $teaser)
            {
                $io->progressAdvance();
                if (!$teaser->teaser_page_link && !$teaser->teaser_fragment_identifier)
                {
                    continue;
                }
                $teaser->type   = LinkTeaserElement::TYPE;
                $teaser->source = LinkTeaserElement::SOURCE_ARTICLE;
                if (!$this->dryRun)
                {
                    $teaser->save();
                }
            }
            $io->progressFinish();
        }

        $io->success("Finished ");
        return 0;
    }

    protected function migrateFromModule(SymfonyStyle $io)
    {
        $pageTeaser = ContentModel::findByType(LinkTeaserElement::TYPE);

        if (!$pageTeaser) {
            $io->text("Found no linkteaser element to migrate.");
            return 0;
        }

        $io->text("Found ". $pageTeaser->count() ." linkteaser entries in tl_content.");

        if (!$this->directMigration) {
            if (!$io->confirm('Start migrating database?'))
            {
                $this->finishedWithoutChanges($io);
                return 0;
            }
        }

        $io->progressStart($pageTeaser->count());
        foreach ($pageTeaser as $teaser)
        {
            $io->progressAdvance();
            $teaser->article = $teaser->articleId;
            if (!$this->dryRun) {
                $teaser->save();
            }
        }
        $io->progressFinish();

        $io->success("Finished database upgrade to teaser bundle.");
        return 0;
    }
}
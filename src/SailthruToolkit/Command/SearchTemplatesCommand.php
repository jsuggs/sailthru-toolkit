<?php

namespace SailthruToolkit\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SearchTemplatesCommand extends AbstractSailThruCommand
{
    private $templates;

    protected function configure()
    {
        parent::configure();

        $this
            ->setDescription('Download all templates')
            ->addArgument('env',      InputArgument::REQUIRED, 'The env to download')
            ->addArgument('search',   InputArgument::REQUIRED, 'Search term')
            ->addArgument('property', InputArgument::OPTIONAL, 'What property to search', 'content_html')
            ->addOption('search-directory', 'd', InputOption::VALUE_OPTIONAL, 'The directory to download into (if provided will override value in config.yml)')
            ->addOption('search-revisions', 'r', InputOption::VALUE_NONE, 'If supplied, then will search all of the revisions')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);

        $baseDir = $input->getOption('search-directory') ?: $this->getParameter('template_dir');

        // Remove trailing slash
        if (substr($baseDir, -1) === '/') {
            $baseDir = substr($baseDir, 0, -1);
        }

        $env = $input->getArgument('env');
        $dir = sprintf('%s/%s', $baseDir, $env);

        if (!file_exists($dir)) {
            throw new \RuntimeException(sprintf('The directory <error>"%s"</error> does not exist, you\'ll need to download the templates locally first', $dir));
        }

        // TODO Use Finder Component
        $searchRevisions = $input->getOption('search-revisions');
        $this->templates = array_map(function($basePath) use ($searchRevisions) {
            $templateRevisions = glob(sprintf('%s/*', escapeshellcmd($basePath)));

            $basePathLen = strlen($basePath) + 1;
            $revisionArray = array_combine(array_map(function($revision) use ($basePathLen) { return substr($revision, $basePathLen); }, $templateRevisions), $templateRevisions);
            ksort($revisionArray, SORT_NUMERIC);

            $revisionsToSearch = $searchRevisions
                ? $revisionArray
                : array(end($revisionArray));

            return array_map(function($fileName) {
                $data = file_get_contents($fileName);
                return json_decode($data, true);
            }, $revisionsToSearch);
        }, glob(sprintf('%s/*', escapeshellcmd($dir))));

        $output->writeln(sprintf('Searching templates from %s for %s', $env, $input->getArgument('search')));
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        foreach ($this->templates as $templateSet) {
            foreach ($templateSet as $template) {
                if (strstr($template[$input->getArgument('property')], $input->getArgument('search'))) {
                    $input->getOption('search-revisions')
                        ? $output->writeln(sprintf('Template %s revision %d matches', $template['name'], array_key_exists('revision_id', $template) ? $template['revision_id'] : 'Unknown'))
                        : $output->writeln(sprintf('Template %s matches', $template['name']));
                }
            }
        }
    }

    public function getCommandName()
    {
        return 'search-templates';
    }
}

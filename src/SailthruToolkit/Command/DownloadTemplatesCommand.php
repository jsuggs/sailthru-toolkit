<?php

namespace SailthruToolkit\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DownloadTemplatesCommand extends AbstractSailThruCommand
{
    private $dir;
    private $env;
    private $client;

    protected function configure()
    {
        parent::configure();

        $this
            ->setDescription('Download all templates')
            ->addArgument('env', InputArgument::REQUIRED, 'The env to download')
            ->addOption('download-directory', 'd', InputOption::VALUE_OPTIONAL, 'The directory to download into (if provided will override value in config.yml)')
            ->addOption('download-revisions', 'r', InputOption::VALUE_NONE, 'If supplied, then will download all revisions')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);

        $baseDir = $input->getOption('download-directory') ?: $this->getParameter('template_dir');

        // Remove trailing slash
        if (substr($baseDir, -1) === '/') {
            $baseDir = substr($baseDir, 0, -1);
        }

        $this->env = $input->getArgument('env');
        $this->dir = sprintf('%s/%s', $baseDir, $this->env);

        $this->createDirectory($output, $this->dir);

        $this->client = $this->getSailThruClient($input->getArgument('env'));

        $output->writeln(sprintf('Downloading templates from <error>%s</error> into <comment>%s</comment>', $this->env, $this->dir));
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $templates = $this->client->getTemplates();
        foreach ($templates['templates'] as $template) {
            $output->writeln(sprintf('<comment>%s</comment>', $template['name']));

            $input->getOption('download-revisions')
                ? $this->downloadTemplateRevisions($output, $template['name'])
                : $this->downloadTemplate($output, $template['name']);
        }
        $output->writeln("<info>done</info>");
    }

    public function getCommandName()
    {
        return 'download-templates';
    }

    protected function downloadTemplateRevisions(OutputInterface $output, $templateName)
    {
        $templateData = $this->client->getTemplate($templateName);
        foreach ($templateData['revision_ids'] as $revision) {
            $this->downloadTemplate($output, $templateName, $revision);
        }
        $output->writeln('');
    }

    protected function downloadTemplate(OutputInterface $output, $templateName, $revision = null)
    {
        $normalizedTemplateName = str_replace(' ', '_', strtolower($templateName));

        // Create a directory to hold the data for the templates
        $templateDir = sprintf('%s/%s', $this->dir, $normalizedTemplateName);
        $this->createDirectory($output, $templateDir);

        // Check to see if we already have the revision in question
        if ($revision && file_exists(sprintf('%s/%s', $templateDir, $revision))) {
            OutputInterface::VERBOSITY_VERBOSE === $output->getVerbosity()
                ? $output->writeln(sprintf('<info>Already downloaded revision %d</info>', $revision))
                : $output->write('<info>.</info>');
            return;
        }

        $templateData = $revision
            ? $this->client->getTemplateFromRevision($revision)
            : $this->client->getTemplate($templateName);

        $dataFile = sprintf('%s/%s', $templateDir, $revision ?: $templateData['revision_id']);
        file_put_contents($dataFile, json_encode($templateData, JSON_PRETTY_PRINT));

        OutputInterface::VERBOSITY_VERBOSE === $output->getVerbosity()
            ? $output->writeln(sprintf('<info>Downloaded revision %d</info>', $revision))
            : $output->write('<comment>.</comment>');
    }

    protected function createDirectory(OutputInterface $output, $directory)
    {
        if (!file_exists($directory)) {
            if (OutputInterface::VERBOSITY_VERBOSE === $output->getVerbosity()) {
                $output->writeln(sprintf('Directory <info>%s</info> does not exist.  Attempting to create', $directory));
            }

            if (!mkdir($directory, 0755, true)) {
                throw new \RuntimeException(sprintf('Unable to create directory "%s"', $this->dir));
            }
        }
    }
}

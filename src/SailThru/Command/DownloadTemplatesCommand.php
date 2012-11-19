<?php

namespace SailThru\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
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
            ->addArgument('dir', InputArgument::REQUIRED, 'The directory to download into')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);

        $this->env = $input->getArgument('env');
        $this->dir = $input->getArgument('dir');
        // Remove trailing slash
        if (substr($this->dir, -1) === '/') {
            $this->dir = substr($this->dir, 0, -1);
        }
        $this->dir = sprintf('%s/%s', $this->dir, $this->env);

        if (!file_exists($this->dir)) {
            $output->writeln(sprintf('Directory "%s" does not exist, attempting to create', $this->dir));
            if (!mkdir($this->dir, 0755, true)) {
                throw new \RuntimeException(sprintf('Unable to create directory "%s"', $this->dir));
            }
        }

        // Ensure that directory is empty (*nix only hack)
        system(sprintf('rm -rf %s/*', $this->dir));

        $this->client = $this->getSailThruClient($input->getArgument('env'));

        $output->writeln(sprintf('Downloading templates from %s into %s', $this->env, $this->dir));
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $templates = $this->client->getTemplates();
        foreach ($templates['templates'] as $template) {
            $output->write('.');
            $this->downloadTemplate($template['name']);
        }
        $output->writeln("\n\ndone");
    }

    public function getCommandName()
    {
        return 'download-templates';
    }

    protected function downloadTemplate($templateName)
    {
        $template = $this->client->getTemplate($templateName);
        $fileName = str_replace(' ', '_', strtolower(sprintf('%s/%s', $this->dir, $templateName)));
        file_put_contents($fileName, json_encode($template));
    }
}

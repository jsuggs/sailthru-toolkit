<?php

namespace SailthruToolkit\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ViewSendCommand extends AbstractSailThruCommand
{
    protected $client;

    protected function configure()
    {
        parent::configure();

        $this
            ->setDescription('View a send')
            ->addArgument('env',   InputArgument::REQUIRED, 'The env')
            ->addArgument('sid',   InputArgument::REQUIRED, 'The SID (send_id)')
            ->addOption('file', 'f', InputOption::VALUE_NONE, 'Save the var output to files')
            ->addOption('send-directory', 'd', InputOption::VALUE_OPTIONAL, 'The directory to download into (if provided will override value in config.yml)')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);

        $this->client = $this->getSailThruClient($input->getArgument('env'));
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $response = $this->client->getSend($input->getArgument('sid'));
        $input->getOption('file')
            ? $this->downloadSend($input, $output, $response)
            : var_dump($response);
    }

    protected function downloadSend(InputInterface $input, OutputInterface $output, $sendData)
    {
        $baseDir = $input->getOption('send-directory') ?: $this->getParameter('send_dir');
        // Remove trailing slash
        if (substr($baseDir, -1) === '/') {
            $baseDir = substr($baseDir, 0, -1);
        }

        $this->env = $input->getArgument('env');
        $this->dir = sprintf('%s/%s', $baseDir, $this->env);

        $this->createDirectory($output, $this->dir);

        file_put_contents(sprintf('%s/%s.html', $this->dir, $sendData['send_id']), file_get_contents($sendData['view_url']));
    }

    public function getCommandName()
    {
        return 'view-send';
    }
}

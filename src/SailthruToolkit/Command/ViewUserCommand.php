<?php

namespace SailthruToolkit\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ViewUserCommand extends AbstractSailThruCommand
{
    protected $client;

    protected function configure()
    {
        parent::configure();

        $this
            ->setDescription('View a user')
            ->addArgument('env',   InputArgument::REQUIRED, 'The env')
            ->addArgument('email', InputArgument::REQUIRED, 'The email of the user')
            ->addOption('vars', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'The vars to display (json encoded)')
            ->addOption('file', 'f', InputOption::VALUE_NONE, 'Save the var output to files')
            ->addOption('user-directory', 'd', InputOption::VALUE_OPTIONAL, 'The directory to download into (if provided will override value in config.yml)')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);

        $baseDir = $input->getOption('user-directory') ?: $this->getParameter('user_dir');

        // Remove trailing slash
        if (substr($baseDir, -1) === '/') {
            $baseDir = substr($baseDir, 0, -1);
        }

        $this->env = $input->getArgument('env');
        $this->dir = sprintf('%s/%s/%s', $baseDir, $this->env, $input->getArgument('email'));

        $this->createDirectory($output, $this->dir);

        $this->client = $this->getSailThruClient($input->getArgument('env'));
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $response = $this->client->getUseBySid($input->getArgument('email'));
        $this->displayResponse($response);
        if ($vars = $input->getOption('vars')) {
            foreach ($vars as $var) {
                $json = json_encode(array($var => $response['vars'][$var]), JSON_PRETTY_PRINT);
                //$output->writeln(sprintf('<comment>%s</comment>: <info>%s</info>' , $var, $json));

                if ($input->getOption('file')) {
                    file_put_contents(sprintf('%s/%s.json', $this->dir, $var), $json);
                }
            }
        } else {
            var_dump($response);

            if ($input->getOption('file')) {
                file_put_contents(sprintf('%s/%s.json', $this->dir, 'ALL'), $response);
            }
        }
    }

    public function getCommandName()
    {
        return 'view-user';
    }
}

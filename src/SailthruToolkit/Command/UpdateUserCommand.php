<?php

namespace SailthruToolkit\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateUserCommand extends AbstractSailThruCommand
{
    private $client;
    private $vars;

    protected function configure()
    {
        parent::configure();

        $this
            ->setDescription('Update a users profile vars')
            ->addArgument('env',    InputArgument::REQUIRED, 'The env to update')
            ->addArgument('email',  InputArgument::REQUIRED, 'The email of the user to update')
            ->addArgument('vars',   InputArgument::OPTIONAL, 'The vars (JSON formatted)', '[]')
            ->addOption('files', 'f', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'A set of json encoded file')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);

        $this->client = $this->getSailThruClient($input->getArgument('env'));

        $this->vars = array();
        if ($vars = $input->getArgument('vars')) {
            $this->vars = array_merge($this->vars, json_decode($vars, true));
        }

        foreach ($input->getOption('files') as $file) {
            $this->vars = array_merge($this->vars, json_decode(file_get_contents($file), true));
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(sprintf(
            'Updating profile for "%s" in env: %s',
            $input->getArgument('email'),
            $input->getArgument('env')
        ));

        $data = array(
            'id' => $input->getArgument('email'),
            'key' => 'email',
            'vars' => $this->vars,
        );

        $response = $this->client->apiPost('user', $data);
        $this->displayResponse($response);
    }

    public function getCommandName()
    {
        return 'update-user';
    }
}

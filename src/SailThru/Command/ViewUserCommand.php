<?php

namespace SailThru\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
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
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);

        $this->client = $this->getSailThruClient($input->getArgument('env'));
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $response = $this->client->getUseBySid($input->getArgument('email'));
        $this->displayResponse($response);
        var_dump($response);
    }

    public function getCommandName()
    {
        return 'view-user';
    }
}

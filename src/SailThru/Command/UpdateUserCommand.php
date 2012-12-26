<?php

namespace SailThru\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateUserCommand extends AbstractSailThruCommand
{
    private $client;

    protected function configure()
    {
        parent::configure();

        $this
            ->setDescription('Update a users profile vars')
            ->addArgument('env',    InputArgument::REQUIRED, 'The env to update')
            ->addArgument('email',  InputArgument::REQUIRED, 'The email of the user to update')
            ->addArgument('vars',   InputArgument::OPTIONAL, 'The vars (JSON formatted)', '[]')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);

        $this->client = $this->getSailThruClient($input->getArgument('env'));
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
            'vars' => json_decode($input->getArgument('vars'), true),
        );

        $response = $this->client->apiPost('user', $data);
        $this->displayResponse($response);
    }

    public function getCommandName()
    {
        return 'update-user';
    }
}

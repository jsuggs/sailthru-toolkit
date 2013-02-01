<?php

namespace SailthruToolkit\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateOptoutCommand extends AbstractSailThruCommand
{
    private $client;
    private $status;
    protected static $validStatuses = array(
        'none',
        'all',
        'blast',
    );

    protected function configure()
    {
        parent::configure();

        $this
            ->setDescription('Update a users optout status')
            ->addArgument('env',    InputArgument::REQUIRED, 'The env to update')
            ->addArgument('email',  InputArgument::REQUIRED, 'The email of the user to update')
            ->addArgument('status', InputArgument::REQUIRED, 'The users optout status (none|all|blast)')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);

        $this->status = $input->getArgument('status');
        if (!in_array($this->status, self::$validStatuses)) {
            throw new \InvalidArgumentException(sprintf('%s is not a valid status, only %s', $this->status, implode(', ', self::$validStatuses)));
        }

        $this->client = $this->getSailThruClient($input->getArgument('env'));
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(sprintf(
            'Updating status for "%s" to "%s" in env: %s',
            $input->getArgument('email'),
            $input->getArgument('status'),
            $input->getArgument('env')
        ));

        $data = array(
            'id' => $input->getArgument('email'),
            'key' => 'email',
            'optout_email' => $this->status,
        );

        $response = $this->client->apiPost('user', $data);
        $this->displayResponse($response);
    }

    public function getCommandName()
    {
        return 'update-optout';
    }
}

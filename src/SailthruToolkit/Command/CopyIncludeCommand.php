<?php

namespace SailthruToolkit\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CopyIncludeCommand extends AbstractSailThruCommand
{
    private $fromClient;
    private $toClient;

    protected function configure()
    {
        parent::configure();

        $this
            ->setDescription('Copy a SailThru include')
            ->addArgument('from-env',      InputArgument::REQUIRED, 'The env to copy from')
            ->addArgument('to-env',        InputArgument::REQUIRED, 'The env to copy to')
            ->addArgument('include-name',  InputArgument::REQUIRED, 'The include to copy')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);

        $this->fromClient = $this->getSailThruClient($input->getArgument('from-env'));
        $this->toClient = $this->getSailThruClient($input->getArgument('to-env'));
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(sprintf(
            'Copying include %s from %s to %s',
            $input->getArgument('include-name'),
            $input->getArgument('from-env'),
            $input->getArgument('to-env')
        ));

        $include = $this->fromClient->getInclude($input->getArgument('include-name'));
        $response = $this->toClient->saveInclude($input->getArgument('include-name'), $include);
        $this->displayResponse($response);
    }

    public function getCommandName()
    {
        return 'copy-include';
    }
}

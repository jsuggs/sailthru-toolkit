<?php

namespace SailthruToolkit\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ViewListCommand extends AbstractSailThruCommand
{
    protected $api;

    protected function configure()
    {
        parent::configure();

        $this
            ->setDescription('View a list')
            ->addArgument('env',    InputArgument::REQUIRED, 'The env')
            ->addArgument('list',   InputArgument::REQUIRED, 'The list')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);

        $this->api = $this->getSailThruClient('prod');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $response = $this->api->getList($input->getArgument('list'));
        var_dump($response);
    }

    public function getCommandName()
    {
        return 'view-list';
    }
}

<?php

namespace SailthruToolkit\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DeleteListCommand extends AbstractSailThruCommand
{
    private $client;
    private $lists;

    protected function configure()
    {
        parent::configure();

        $this
            ->setDescription('Delete one or more lists from SailThru')
            ->addArgument('env',  InputArgument::REQUIRED, 'The env to delete from')
            ->addArgument('list', InputArgument::REQUIRED, 'The list to delete, or a regex to match against all lists')
            ->addOption('regex',  'r', InputOption::VALUE_NONE, 'Is the include a regex')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);

        $this->client = $this->getSailThruClient($input->getArgument('env'));
        $this->lists = array();

        // Check to see if we are using regexes
        if ($input->getOption('regex')) {
            // Loop over all of the includes on the from client
            foreach ($this->client->getlists()['lists'] as $list) {
                // If it matches, then add it to the lists to be deleted
                if (preg_match($input->getArgument('list'), $list['name'])) {
                    $this->lists[] = $list['name'];
                }
            }
        } else {
            // Just add the passed in list
            $this->lists[] = $input->getArgument('list');
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        foreach ($this->lists as $list) {
            $output->writeln(sprintf(
                'Deleting list %s from %s',
                $list,
                $input->getArgument('env')
            ));

            $response = $this->client->deleteList($list);
            $this->displayResponse($response);
        }
    }

    public function getCommandName()
    {
        return 'delete-list';
    }
}

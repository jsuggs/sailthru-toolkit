<?php

namespace SailthruToolkit\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CopyIncludeCommand extends AbstractSailThruCommand
{
    private $fromClient;
    private $toClient;
    private $includes;

    protected function configure()
    {
        parent::configure();

        $this
            ->setDescription('Copy a SailThru include')
            ->addArgument('from-env', InputArgument::REQUIRED, 'The env to copy from')
            ->addArgument('to-env',   InputArgument::REQUIRED, 'The env to copy to')
            ->addArgument('include',  InputArgument::REQUIRED, 'The include to copy, or a regex to match against all includes in the from env')
            ->addOption('regex',  'r', InputOption::VALUE_NONE, 'Is the include a regex')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);

        $this->fromClient = $this->getSailThruClient($input->getArgument('from-env'));
        $this->toClient = $this->getSailThruClient($input->getArgument('to-env'));

        // Check to see if we are using regexes
        if ($input->getOption('regex')) {
            // Loop over all of the includes on the from client
            foreach ($this->fromClient->getIncludes()['includes'] as $include) {
                // If it matches, then add it to the list to be copied
                if (preg_match($input->getArgument('include'), $include['name'])) {
                    $this->includes[] = $include['name'];
                }
            }
        } else {
            // Just add the passed in include
            $this->includes[] = $input->getArgument('include');
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        foreach ($this->includes as $includeName) {
            $output->writeln(sprintf(
                'Copying include %s from %s to %s',
                $includeName,
                $input->getArgument('from-env'),
                $input->getArgument('to-env')
            ));

            $include = $this->fromClient->getInclude($includeName);
            $response = $this->toClient->saveInclude($includeName, $include);
            $this->displayResponse($response);
        }
    }

    public function getCommandName()
    {
        return 'copy-include';
    }
}

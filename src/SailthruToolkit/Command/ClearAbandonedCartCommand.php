<?php

namespace SailthruToolkit\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ClearAbandonedCartCommand extends AbstractSailThruCommand
{
    private $client;
    private $vars;

    protected function configure()
    {
        parent::configure();

        $this->setDescription('Clear an abandoned cart')
            ->addArgument('env', InputArgument::REQUIRED, 'The env to update')
            ->addArgument('email', InputArgument::REQUIRED, 'The email of the user to update');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);
        $this->client = $this->getSailThruClient($input->getArgument('env'));
    }

    /**
     * Uses purchase request to clear an abandoned shopping cart per Abandoned
     * Shopping Cart documentation: http://docs.sailthru.com/documentation/email-features/abandoned-shopping-cart
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(sprintf(
            'Clearing abandoned cart for "%s" in env: %s',
            $input->getArgument('email'),
            $input->getArgument('env')
        ));

        $response = $this->client->purchase(
            $input->getArgument('email'),
            $items = array(),
            $incomplete = 1
        );

        $this->displayResponse($response);
    }

    public function getCommandName()
    {
        return 'clear-abandoned-cart';
    }
}

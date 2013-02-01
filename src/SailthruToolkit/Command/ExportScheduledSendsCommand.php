<?php

namespace SailthruToolkit\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExportScheduledSendsCommand extends AbstractSailThruCommand
{
    private $client;

    protected function configure()
    {
        parent::configure();

        $this
            ->setDescription('Export the scheduled sends')
            ->addArgument('env',   InputArgument::REQUIRED, 'The env to update')
            ->addArgument('email', InputArgument::OPTIONAL, 'The email to send the report to')
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
            'Exporting the scheduled sends for env: %s. Sending to "%s"',
            $input->getArgument('env'),
            $input->getArgument('email')
        ));

        $options = array(
            'job' => 'export_scheduled_sends',
        );

        if ($input->getArgument('email')) {
            $options['report_email'] = $input->getArgument('email');
        }

        $response = $this->client->apiPost('job', $options);
        $this->displayResponse($response);
    }

    public function getCommandName()
    {
        return 'export-scheduled-sends';
    }
}

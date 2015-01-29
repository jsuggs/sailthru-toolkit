<?php

namespace SailthruToolkit\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ListCampaignsCommand extends AbstractSailThruCommand
{
    private $env;
    private $client;

    protected function configure()
    {
        parent::configure();

        $this
            ->setDescription('List recent campaigns')
            ->addArgument('env', InputArgument::REQUIRED, 'The env to download')
            ->addArgument('start-date', InputArgument::OPTIONAL, 'The start date for campaigns', '-3 hour')
            ->addArgument('end-date', InputArgument::OPTIONAL, 'The end start date for campaigns', 'now')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);

        $this->client = $this->getSailThruClient($input->getArgument('env'));
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $startDate = new \DateTime($input->getArgument('start-date'));
        $endDate = new \DateTime($input->getArgument('end-date'));

        $blasts = $this->client->getBlasts(array(
            'start_date' => $startDate->format('r'),
            'end_date' => $startDate->format('r'),
            'status' => 'sent',
        ));

        $data = array_map(function($blast) {
            return array(
                $blast['blast_id'],
                $blast['name'],
                number_format($blast['email_count']),
            );
        }, $blasts['blasts']);

        $table = $this->getHelper('table');
        $table
            ->setHeaders(array('ID', 'Name', 'Email Count'))
            ->setRows($data);
        $table->render($output);
    }

    public function getCommandName()
    {
        return 'list-campaigns';
    }
}

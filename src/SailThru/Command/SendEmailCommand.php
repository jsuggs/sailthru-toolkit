<?php

namespace SailThru\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SendEmailCommand extends AbstractSailThruCommand
{
    private $fromClient;
    private $toClient;
    private $templateName;

    protected function configure()
    {
        parent::configure();

        $this
            ->setDescription('Send an email')
            ->addArgument('env',           InputArgument::REQUIRED, 'The env to update')
            ->addArgument('email',         InputArgument::REQUIRED, 'The email of the user to update')
            ->addArgument('template',      InputArgument::REQUIRED, 'The users mobile number')
            ->addArgument('schedule_time', InputArgument::OPTIONAL, 'When to send the email')
            ->addArgument('vars',          InputArgument::OPTIONAL, 'The vars (JSON formatted)', '[]')
            ->addArgument('options',       InputArgument::OPTIONAL, 'The options (JSON formatted)', '[]')
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
            'Sending email "%s" template "%s" in env: %s with params: "%s" options: "%s" at time: "%s"',
            $input->getArgument('email'),
            $input->getArgument('template'),
            $input->getArgument('env'),
            $input->getArgument('vars'),
            $input->getArgument('options'),
            $input->getArgument('schedule_time')
        ));

        $response = $this->client->send(
            $input->getArgument('template'),
            $input->getArgument('email'),
            json_decode($input->getArgument('vars'), true),
            json_decode($input->getArgument('options'), true),
            $input->getArgument('schedule_time')
        );

        $this->displayResponse($response);
    }

    public function getCommandName()
    {
        return 'send-email';
    }
}

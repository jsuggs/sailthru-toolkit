<?php

namespace SailThru\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UploadJobCommand extends AbstractSailThruCommand
{
    private $client;

    protected function configure()
    {
        parent::configure();

        $this
            ->setDescription('Upload a job')
            ->addArgument('env',    InputArgument::REQUIRED, 'The env to update')
            ->addArgument('email',  InputArgument::REQUIRED, 'The email to notifiy on completion')
            ->addArgument('file',   InputArgument::REQUIRED, 'The file to upload')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);

        $this->client = $this->getSailThruClient($input->getArgument('env'));
        if (!file_exists($input->getArgument('file'))) {
            throw new \RuntimeException(sprintf('The file %s does not exist'));
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(sprintf(
            'Uploading file "%s" to env: %s',
            $input->getArgument('file'),
            $input->getArgument('env')
        ));

        $response = $this->client->apiPost('job', array(
            'job' => 'update',
            'file' => $input->getArgument('file'),
            'report_email' => $input->getArgument('email'),
        ), array(
          'file'
        ));

        $this->displayResponse($response);
    }

    public function getCommandName()
    {
        return 'upload-job';
    }
}

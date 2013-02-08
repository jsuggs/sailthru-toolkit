<?php

namespace SailthruToolkit\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UploadJobCommand extends AbstractSailThruCommand
{
    private $client;
    private $files;

    protected function configure()
    {
        parent::configure();

        $this
            ->setDescription('Upload a job')
            ->addArgument('env',      InputArgument::REQUIRED, 'The env to update')
            ->addArgument('file',     InputArgument::REQUIRED, 'The file to upload')
            ->addArgument('max-size', InputArgument::OPTIONAL, 'The max size of the file to upload (in MB)', '5')
            ->addArgument('email',    InputArgument::OPTIONAL, 'The email to notifiy on completion')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);

        $this->client = $this->getSailThruClient($input->getArgument('env'));
        if (!file_exists($input->getArgument('file'))) {
            throw new \RuntimeException(sprintf('The file %s does not exist'));
        }

        $filesize = filesize($input->getArgument('file'));
        $output->writeln(sprintf('The file is %d bytes', $filesize));
        if (floor($filesize / 1024) > $input->getArgument('max-size')) {
            $tmpDir = sys_get_temp_dir() . '/sailthru';
            $filePrefix = 'chunk';

            if (is_dir($tmpDir)) {
                // Clean it out
            } else {
                mkdir($tmpDir);
            }

            // TODO Make cross platform
            exec(sprintf('split --line-bytes=%dm --verbose %s %s/%s 2>&1', $input->getArgument('max-size'), $input->getArgument('file'), $tmpDir, $filePrefix));

            // TODO Use Finder Component
            $this->files = glob(sprintf('%s/%s*', $tmpDir, $filePrefix));
            $output->writeln(sprintf('The file will be uploaded in %d chunks', count($this->files)));
        } else {
            $this->files = array($input->getArgument('file'));
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        foreach ($this->files as $file) {
            $output->writeln(sprintf(
                'Uploading file "%s" to env: %s',
                $file,
                $input->getArgument('env')
            ));

            $options = array(
                'job' => 'update',
                'file' => $file,
            );

            if ($input->getArgument('email')) {
                $options['report_email'] = $input->getArgument('email');
            }

            $response = $this->client->apiPost('job', $options, array('file'));
            $this->displayResponse($response);
        }
    }

    public function getCommandName()
    {
        return 'upload-job';
    }
}

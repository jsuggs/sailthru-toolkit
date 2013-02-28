<?php

namespace SailthruToolkit\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateUserKeysCommand extends AbstractSailThruCommand
{
    private $client;
    private $vars;

    protected function configure()
    {
        parent::configure();

        $this
            ->setDescription('Update a users keys')
            ->addArgument('env',  InputArgument::REQUIRED, 'The env to update')
            ->addArgument('file', InputArgument::REQUIRED, 'A csv file with email, extid')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);

        if (!file_exists($input->getArgument('file'))) {
            throw new \RuntimeException(sprintf('The file %s does not exist'), $input->getArgument('file'));
        }
        $this->client = $this->getSailThruClient($input->getArgument('env'));
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $fh = fopen($input->getArgument('file'), 'r');

        while ($data = fgetcsv($fh)) {
            $output->writeln(sprintf(
                'Updating user keys for "%s" in env: %s',
                $data[0],
                $input->getArgument('env')
            ));

            $data = array(
                'id' => $data[0],
                'key' => 'email',
                'keys' => array('extid' => $data[1]),
            );

            $response = $this->client->apiPost('user', $data);
            $this->displayResponse($response);
        }
        fclose($fh);
    }

    public function getCommandName()
    {
        return 'update-user-keys';
    }
}

<?php

namespace SailthruToolkit\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateMobileCommand extends AbstractSailThruCommand
{
    private $fromClient;
    private $toClient;
    private $templateName;

    protected function configure()
    {
        parent::configure();

        $this
            ->setDescription('Update a users mobile number')
            ->addArgument('env',    InputArgument::REQUIRED, 'The env to update')
            ->addArgument('email',  InputArgument::REQUIRED, 'The email of the user to update')
            ->addArgument('mobile', InputArgument::REQUIRED, 'The users mobile number')
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
            'Updating mobile for "%s" to %s in env: %s',
            $input->getArgument('email'),
            $input->getArgument('mobile'),
            $input->getArgument('env')
        ));

        $data = array(
            'id' => $input->getArgument('email'),
            'key' => 'email',
            'keys' => array(
                'sms' => $input->getArgument('mobile')
            ),
        );

        $response = $this->client->apiPost('user', $data);
        $this->displayResponse($response);
    }

    public function getCommandName()
    {
        return 'update-mobile';
    }
}

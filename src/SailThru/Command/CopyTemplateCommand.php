<?php

namespace SailThru\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CopyTemplateCommand extends AbstractSailThruCommand
{
    private $fromClient;
    private $toClient;
    private $templateName;

    protected function configure()
    {
        parent::configure();

        $this
            ->setDescription('Copy a SailThru template')
            ->addArgument('from-env',      InputArgument::REQUIRED, 'The env to copy from')
            ->addArgument('to-env',        InputArgument::REQUIRED, 'The env to copy to')
            ->addArgument('template-name', InputArgument::REQUIRED, 'The template to copy')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);

        $this->fromClient = $this->getSailThruClient($input->getArgument('from-env'));
        $this->toClient = $this->getSailThruClient($input->getArgument('to-env'));

        $this->templateName = $input->getArgument('template-name');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(sprintf(
            'Copying template %s from %s to %s',
            $input->getArgument('template-name'),
            $input->getArgument('from-env'),
            $this->templateName
        ));

        $fromTemplate = $this->fromClient->getTemplate($this->templateName);
        $response = $this->toClient->saveTemplate($this->templateName, $fromTemplate);
        $this->displayResponse($response);
    }

    public function getCommandName()
    {
        return 'copy-template';
    }
}

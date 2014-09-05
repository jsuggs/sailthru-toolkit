<?php

namespace SailthruToolkit\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CopyTemplateCommand extends AbstractSailThruCommand
{
    private $fromClient;
    private $toClient;

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

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $templateName = $input->getArgument('template-name');

        $output->writeln(sprintf(
            'Copying template %s from %s to %s',
            $templateName,
            $input->getArgument('from-env'),
            $input->getArgument('to-env')
        ));

        $fromTemplate = $this->fromClient->getTemplate($templateName);
        $data = $this->formatTemplateData($fromTemplate);
        $response = $this->toClient->saveTemplate($templateName, $data);
        $this->displayResponse($response);
    }

    protected function formatTemplateData($data)
    {
        $currentLabels = array_key_exists('labels', $data)
            ? $data['labels']
            : array();

        $labels = array();
        foreach ($currentLabels as $label) {
            $labels[$label] = 1;
        }
        $data['labels'] = $labels;

        return $data;
    }

    public function getCommandName()
    {
        return 'copy-template';
    }
}

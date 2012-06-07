<?php

namespace SailThru\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractSailThruCommand extends Command
{
    const TEMPLATE_PREFIX = '[v2]';

    protected $app;
    protected $output;
    protected $clients;

    abstract public function getCommandName();

    public function __construct(\Silex\Application $app)
    {
        parent::__construct();

        $this->app = $app;
    }

    protected function configure()
    {
        parent::configure();

        $this
            ->setName(sprintf('sailthru:%s', $this->getCommandName()));
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);

        $this->output = $output;
        $this->clients = array();
    }

    protected function getSailThruClient($env)
    {
        if (!array_key_exists($env, $this->clients)) {
            // Get config for the env
            $config = $this->app['api-keys'];
            if (!array_key_exists($env, $config)) {
                throw new \RuntimeException(sprintf('The env "%s" was not found in /config/api-keys.yml', (string) $env));
            }

            $this->clients[$env] = new \Sailthru_Client($config[$env]['api-key'], $config[$env]['api-secret']);
        }

        return $this->clients[$env];
    }

    protected function findTemplateByName(\Sailthru_Client $client, $templateName)
    {
        // Get a list of all of the templates for this client
        $templates = $client->getTemplates();

        $templateNamePrefixed = sprintf('%s%s', self::TEMPLATE_PREFIX, $templateName);

        $found = array_filter($templates['templates'], function ($template) use ($templateName, $templateNamePrefixed) {
            return in_array($template['name'], array($templateName, $templateNamePrefixed));
        });

        if (1 === sizeof($found)) {
            $template = array_pop($found);
            return $template['name'];
        }

        throw new \InvalidArgumentException(sprintf('Neither template %s or %s were found', $templateName, $templateNamePrefixed));
    }

    protected function displayResponse($response)
    {
        if (isset($response['error'])) {
            $this->output->writeln(sprintf('API Error: Code: %d Msg: %s', $response['error'], $response['errormsg']));
        } else {
            $this->output->writeln('API Success');
        }
    }
}

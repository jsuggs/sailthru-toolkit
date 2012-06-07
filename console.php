#!/usr/bin/env php
<?php

//Bootstrap our Silex application
$app = require __DIR__ . '/app/app.php';

// The console app
$console = new Symfony\Component\Console\Application('SailThru Awesomeness', '0.1');

// Add the console commands here
$console->add(new SailThru\Command\CopyTemplateCommand($app));
$console->run();

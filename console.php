#!/usr/bin/env php
<?php

//Bootstrap our Silex application
$app = require __DIR__ . '/app/app.php';

// The console app
$console = new Symfony\Component\Console\Application('Sailthru Awesomeness', '0.1');

// Add the console commands here
$console->add(new SailthruToolkit\Command\CopyIncludeCommand($app));
$console->add(new SailthruToolkit\Command\CopyTemplateCommand($app));
$console->add(new SailthruToolkit\Command\DeleteListCommand($app));
$console->add(new SailthruToolkit\Command\DownloadTemplatesCommand($app));
$console->add(new SailthruToolkit\Command\ExportScheduledSendsCommand($app));
$console->add(new SailthruToolkit\Command\SearchTemplatesCommand($app));
$console->add(new SailthruToolkit\Command\SendEmailCommand($app));
$console->add(new SailthruToolkit\Command\TemplateStatisticsReportCommand($app));
$console->add(new SailthruToolkit\Command\UpdateMobileCommand($app));
$console->add(new SailthruToolkit\Command\UpdateOptoutCommand($app));
$console->add(new SailthruToolkit\Command\UpdateUserCommand($app));
$console->add(new SailthruToolkit\Command\UpdateUserKeysCommand($app));
$console->add(new SailthruToolkit\Command\UploadJobCommand($app));
$console->add(new SailthruToolkit\Command\ViewListCommand($app));
$console->add(new SailthruToolkit\Command\ViewSendCommand($app));
$console->add(new SailthruToolkit\Command\ViewUserCommand($app));
$console->run();

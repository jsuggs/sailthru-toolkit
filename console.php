#!/usr/bin/env php
<?php

//Bootstrap our Silex application
$app = require __DIR__ . '/app/app.php';

// The console app
$console = new Symfony\Component\Console\Application('Sailthru Awesomeness', '0.2');

$finder = new Symfony\Component\Finder\Finder();
$finder->files()->in(__DIR__ . '/src')->name('*Command.php');

foreach ($finder as $file) {
    $class = sprintf('SailthruToolkit\Command\%s', $file->getBasename('.php'));
    $r = new \ReflectionClass($class);
    if ($r->isSubclassOf('SailthruToolkit\Command\AbstractSailThruCommand') && !$r->isAbstract()) {
        $console->add($r->newInstance($app));
    }
}
$console->run();

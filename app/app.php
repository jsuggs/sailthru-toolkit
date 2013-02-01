<?php

require_once __DIR__ . '/../vendor/autoload.php';

$app = new Silex\Application();

$app['api-keys'] = $app->share(function() {
    $parser = new Symfony\Component\Yaml\Parser();
    return $parser->parse(file_get_contents(__DIR__ . '/../config/api-keys.yml'));
});

$app['config'] = $app->share(function() {
    $parser = new Symfony\Component\Yaml\Parser();
    return $parser->parse(file_get_contents(__DIR__ . '/../config/config.yml'));
});

return $app;

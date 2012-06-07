<?php

require_once __DIR__ . '/bootstrap.php';

$app = new Silex\Application();
$app['autoloader']->registerNamespace('Symfony',  __DIR__ . '/../vendor');
$app['autoloader']->registerNamespace('SailThru', __DIR__ . '/../src');

$app['api-keys'] = $app->share(function() {
    $parser = new Symfony\Component\Yaml\Parser();
    return $parser->parse(file_get_contents(__DIR__ . '/../config/api-keys.yml'));
});

$directFileLoader = new Symfony\Component\ClassLoader\MapClassLoader(array(
    'Sailthru_Util'             => __DIR__.'/../vendor/SailThru/sailthru/Sailthru_Util.php',
    'Sailthru_Client'           => __DIR__.'/../vendor/SailThru/sailthru/Sailthru_Client.php',
    'Sailthru_Client_Exception' => __DIR__.'/../vendor/SailThru/sailthru/Sailthru_Client_Exception.php',
));
$directFileLoader->register();

return $app;

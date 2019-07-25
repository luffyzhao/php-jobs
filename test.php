<?php
use Job\Container;
use Job\ServiceProvider;
use Job\WorkCommand;
use Symfony\Component\Console\Application;

include __DIR__ . '/vendor/autoload.php';

$app = new Container();

$app->instance('config.path', __DIR__ . '/config/');

$provider = new ServiceProvider($app);

$provider->register();

$app['queue']->connection('beanstalkd')->push(new \Job\Job);

$application = new Application();

$application->add(new WorkCommand($app));

$application->run();






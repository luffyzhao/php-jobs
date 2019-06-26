<?php
include '../vendor/autoload.php';

use Job\Behaviors\Test;
use Job\Exceptions\ConnectionException;
use Job\Exceptions\PushException;
use Job\Factory\PheanstalkFactory;
use Job\Credentials;

try {
    $client = new PheanstalkFactory(
        new Credentials(
            'beanstalkd',
            11300,
            null,
            5,
            5,
            'test-env'
        )
    );

    $client->push(new Test());

} catch (ConnectionException $e) {

} catch (PushException $e) {

}




<?php
include '../../vendor/autoload.php';

use Job\Behaviors\Test;
use Job\Exceptions\ConnectionException;
use Job\Exceptions\PushException;
use Job\Credentials;
use Job\Factory\RedisFactory;

try {
    $client = new RedisFactory(
        new Credentials(
            'redis',
            6379,
            null,
            'test-env'
        )
    );

    $client->push(new Test());

} catch (ConnectionException $e) {

} catch (PushException $e) {

}




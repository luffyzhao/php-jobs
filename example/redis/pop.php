<?php
include '../../vendor/autoload.php';

use Job\Exceptions\ConnectionException;
use Job\Factory\RedisFactory;
use Job\Credentials;
use Job\Worker;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

try {
    $client = new RedisFactory(
        new Credentials(
            'redis',
            6379,
            null,
            'test-env'
        )
    );

    $worker = new Worker($client);

    try {
        $worker->pushLoggerHandler(new StreamHandler(__DIR__ . '/../logs/worker.log', Logger::WARNING));
    } catch (Exception $e) {
        echo sprintf("开启日志记录失败:%s \n", $e->getMessage());
    }
    $worker->daemon();

} catch (ConnectionException $e) {
    echo sprintf("队列服务器连接失败: %", $e->getMessage());
}

<?php

// autoload_psr4.php @generated by Composer

$vendorDir = dirname(dirname(__FILE__));
$baseDir = dirname($vendorDir);

return array(
    'Symfony\\Polyfill\\Ctype\\' => array($vendorDir . '/symfony/polyfill-ctype'),
    'Ramsey\\Uuid\\' => array($vendorDir . '/ramsey/uuid/src'),
    'Psr\\Log\\' => array($vendorDir . '/psr/log/Psr/Log'),
    'Psr\\Container\\' => array($vendorDir . '/psr/container/src'),
    'Pheanstalk\\' => array($vendorDir . '/pda/pheanstalk/src'),
    'Monolog\\' => array($vendorDir . '/monolog/monolog/src/Monolog'),
    'Job\\' => array($baseDir . '/src'),
    'Interop\\Queue\\' => array($vendorDir . '/queue-interop/queue-interop/src'),
    'Interop\\Amqp\\' => array($vendorDir . '/queue-interop/amqp-interop/src'),
    'Enqueue\\Pheanstalk\\' => array($vendorDir . '/enqueue/pheanstalk'),
    'Enqueue\\Null\\' => array($vendorDir . '/enqueue/null'),
    'Enqueue\\Dsn\\' => array($vendorDir . '/enqueue/dsn'),
    'Enqueue\\' => array($vendorDir . '/enqueue/enqueue'),
);

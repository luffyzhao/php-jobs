<?php


namespace Job;


use Illuminate\Config\Repository;
use Illuminate\Contracts\Bus\Dispatcher as DispatcherContract;
use Illuminate\Contracts\Bus\QueueingDispatcher as QueueingDispatcherContract;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Contracts\Events\Dispatcher as ContractsEventsDispatcher;
use Illuminate\Contracts\Queue\Factory as QueueFactoryContract;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Database\Connectors\ConnectionFactory;
use Illuminate\Database\DatabaseManager;
use Illuminate\Events\Dispatcher;
use Illuminate\Events\Dispatcher as EventsDispatcher;
use Illuminate\Queue\Connectors\BeanstalkdConnector;
use Illuminate\Queue\Connectors\DatabaseConnector;
use Illuminate\Queue\Connectors\NullConnector;
use Illuminate\Queue\Connectors\RedisConnector;
use Illuminate\Queue\Connectors\SyncConnector;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Queue\Failed\DatabaseFailedJobProvider;
use Illuminate\Queue\Failed\NullFailedJobProvider;
use Illuminate\Queue\QueueManager;
use Illuminate\Queue\QueueServiceProvider;
use Illuminate\Queue\Worker;
use Illuminate\Support\Carbon;
use Illuminate\Support\ServiceProvider as SupportServiceProvider;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use SplFileInfo;
use Illuminate\Contracts\Container\Container;
use Symfony\Component\Finder\Finder;
use VladimirYuldashev\LaravelQueueRabbitMQ\Queue\Connectors\RabbitMQConnector;

class ServiceProvider extends SupportServiceProvider implements DeferrableProvider
{
    public function __construct(Container $app)
    {
        parent::__construct($app);
        $this->loadConfiguration($app);
    }

    /**
     * @author luffyzhao@vip.126.com
     */
    public function register()
    {
        $this->registerConnectionServices();
        $this->registerLoggerServices();
        $this->registerExceptionServices();
        $this->registerQueueServices();
        $this->registerEventsServices();
        $this->registerWorker();
        $this->registerBusServices();
    }

    /**
     * @author luffyzhao@vip.126.com
     */
    protected function registerLoggerServices()
    {
        $this->app->singleton('logger', function () {
            return tap(new Logger('mysql'), function (Logger $logger) {
                $config = $this->app['config']->get('logger');
                $logger->pushHandler(
                    new StreamHandler($config['path'], $config['level'] ?? Logger::INFO)
                );
                return $logger;
            });
        });
    }

    /**
     * 注册错误处理
     * @author luffyzhao@vip.126.com
     */
    protected function registerExceptionServices()
    {
        $this->app->singleton(ExceptionHandler::class, function () {
            return new \Job\ExceptionHandler(
                $this->app
            );
        });
    }

    /**
     * Register the queue worker.
     *
     * @return void
     */
    protected function registerWorker()
    {
        $this->app->singleton('queue.worker', function () {
            return new Worker(
                $this->app['queue'], $this->app['events'], $this->app[ExceptionHandler::class]
            );
        });
    }

    /**
     * @author luffyzhao@vip.126.com
     */
    protected function registerBusServices()
    {
        $this->app->singleton(
            \Illuminate\Bus\Dispatcher::class,
            function ($app) {
                return new \Illuminate\Bus\Dispatcher($app);
            }
        );

        $this->app->alias(
            \Illuminate\Bus\Dispatcher::class, DispatcherContract::class
        );

        $this->app->alias(
            \Illuminate\Bus\Dispatcher::class, QueueingDispatcherContract::class
        );

        $this->app->alias('events', EventsDispatcher::class);
        $this->app->alias('events', ContractsEventsDispatcher::class);
    }

    /**
     * 事件注册
     * @author luffyzhao@vip.126.com
     */
    protected function registerEventsServices()
    {
        $this->app->singleton('events', function ($app) {
            return (new Dispatcher($app))->setQueueResolver(function () use ($app) {
                return $app->make(QueueFactoryContract::class);
            });
        });
    }

    /**
     * Register the connectors on the queue manager.
     *
     * @return void
     */
    public function registerQueueServices()
    {
        $this->app->singleton('queue', function ($app) {
            return tap(new QueueManager($app), function ($manager) {
                $provider = new QueueServiceProvider($this->app);
                $provider->registerConnectors($manager);
            });

            if(class_exists(RabbitMQConnector::class)){
                $manager->addConnector('rabbitmq', function () {
                    return new RabbitMQConnector($this->app['events']);
                });
            }
        });

        //注册
        $this->app->singleton('queue.failer', function ($app) {
            $config = $app['config']->get('queue.failer');

            return isset($config['table'])
                ? new DatabaseFailedJobProvider(
                    $app['db'], $config['database'], $config['table']
                )
                : new NullFailedJobProvider;
        });
    }

    /**
     * Register the Null queue connector.
     *
     * @param \Illuminate\Queue\QueueManager $manager
     * @return void
     */
    protected function registerNullConnector($manager)
    {
        $manager->addConnector('null', function () {
            return new NullConnector;
        });
    }

    /**
     * Register the Sync queue connector.
     *
     * @param \Illuminate\Queue\QueueManager $manager
     * @return void
     */
    protected function registerSyncConnector($manager)
    {
        $manager->addConnector('sync', function () {
            return new SyncConnector;
        });
    }

    /**
     * Register the database queue connector.
     *
     * @param \Illuminate\Queue\QueueManager $manager
     * @return void
     */
    protected function registerDatabaseConnector($manager)
    {
        $manager->addConnector('database', function () {
            return new DatabaseConnector($this->app['db']);
        });
    }

    /**
     * Register the Redis queue connector.
     *
     * @param \Illuminate\Queue\QueueManager $manager
     * @return void
     */
    protected function registerRedisConnector($manager)
    {
        $manager->addConnector('redis', function () {
            return new RedisConnector($this->app['redis']);
        });
    }

    /**
     * Register the Beanstalkd queue connector.
     *
     * @param \Illuminate\Queue\QueueManager $manager
     * @return void
     */
    protected function registerBeanstalkdConnector($manager)
    {
        $manager->addConnector('beanstalkd', function () {
            return new BeanstalkdConnector;
        });
    }


    /**
     * 注册databases
     *
     * @return void
     */
    protected function registerConnectionServices()
    {
        $this->app->singleton('db.factory', function ($app) {
            return new ConnectionFactory($app);
        });

        $this->app->singleton('db', function ($app) {
            return new DatabaseManager($app, $app['db.factory']);
        });

        $this->app->bind('db.connection', function ($app) {
            return $app['db']->connection();
        });
    }

    /**
     * @param $app
     * @author luffyzhao@vip.126.com
     */
    protected function loadConfiguration($app)
    {
        $this->app->instance('config', $repository = new Repository([]));
        $files = $this->getConfigurationFiles($app);

        foreach ($files as $key => $path) {
            $repository->set($key, require $path);
        }
    }

    /**
     * Get all of the configuration files for the application.
     *
     * @param Container $app
     * @return array
     */
    protected function getConfigurationFiles(Container $app)
    {
        $files = [];

        $configPath = realpath($app['config.path']);

        foreach (Finder::create()->files()->name('*.php')->in($configPath) as $file) {
            $directory = $this->getNestedDirectory($file, $configPath);

            $files[$directory . basename($file->getRealPath(), '.php')] = $file->getRealPath();
        }

        ksort($files, SORT_NATURAL);

        return $files;
    }

    /**
     * Get the configuration file nesting path.
     *
     * @param \SplFileInfo $file
     * @param string $configPath
     * @return string
     */
    protected function getNestedDirectory(SplFileInfo $file, $configPath)
    {
        $directory = $file->getPath();

        if ($nested = trim(str_replace($configPath, '', $directory), DIRECTORY_SEPARATOR)) {
            $nested = str_replace(DIRECTORY_SEPARATOR, '.', $nested) . '.';
        }

        return $nested;
    }
}
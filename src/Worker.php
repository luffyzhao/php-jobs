<?php


namespace Job;


use Exception;
use Job\Contracts\Factory;
use Job\Contracts\Job;
use Job\Factory\PheanstalkFactory;
use Monolog\Handler\HandlerInterface;
use Monolog\Logger;
use Throwable;

class Worker
{
    /**
     * 是否退出标识。
     * @var bool
     * @author luffyzhao@vip.126.com
     */
    public $shouldQuit = false;

    /**
     * 是否暂停标识
     * @var bool
     * @author luffyzhao@vip.126.com
     */
    public $paused = false;

    /**
     * @var PheanstalkFactory
     * @author luffyzhao@vip.126.com
     */
    private $factory;

    /**
     * @var Logger
     * @author luffyzhao@vip.126.com
     */
    private $loggerHandler;

    public function __construct(Factory $factory)
    {
        $this->factory = $factory;
        $this->loggerHandler = new Logger('worker');
    }

    /**
     * @param $queue
     * @author luffyzhao@vip.126.com
     */
    public function daemon(?string $queue = null)
    {
        if ($this->supportsAsyncSignals()) {
            $this->listenForSignals();
        }

        while (true) {
            $job = $this->getNextJob($queue);

            if ($this->supportsAsyncSignals()) {
                $this->registerTimeoutHandler($job);
            }

            if ($job instanceof Job) {
                $this->runJob($job);
            } else {
                $this->sleep(1);
            }

            $this->stopIfNecessary();
        }
    }

    /**
     * @param HandlerInterface $handler
     * @return Worker
     * @author luffyzhao@vip.126.com
     */
    public function pushLoggerHandler(HandlerInterface $handler)
    {
        $this->loggerHandler->pushHandler($handler);
        return $this;
    }

    /**
     * 杀死进程
     * @param int $status
     * @author luffyzhao@vip.126.com
     */
    public function kill($status = 0)
    {
        if (extension_loaded('posix')) {
            posix_kill(getmypid(), SIGKILL);
        }
        exit($status);
    }

    /**
     * 进程睡眠
     * @param $seconds
     * @author luffyzhao@vip.126.com
     */
    public function sleep($seconds)
    {
        if ($seconds < 1) {
            usleep($seconds * 1000000);
        } else {
            sleep($seconds);
        }
    }

    /**
     * @param Job $job
     * @author luffyzhao@vip.126.com
     */
    private function runJob(Job $job)
    {
        $behavior = $job->getData();
        try {
            $behavior->addRunningCount();
            $behavior->handle();
            $this->factory->acknowledge($job);
        } catch (Exception | Throwable $exception) {

            $behavior->fail($exception);
            $this->factory->acknowledge($job);

            if ($behavior->getTries() > $behavior->getRunningCount()) {

                $this->loggerHandler->error(sprintf(
                    "第 %d 次运行 下次在 %d 秒后运行; %s",
                    $behavior->getRunningCount(),
                    $behavior->getDelay() ? $behavior->getDelay() / 1000 : $behavior->getDelay(),
                    serialize($behavior)
                ));

                try {
                    $this->factory->push($behavior);
                } catch (Exceptions\PushException $exception) {
                    $this->loggerHandler->error($exception->getMessage());
                }

            } else {
                $this->loggerHandler->error(sprintf('最后一次机会用完了; %s', serialize($behavior)));
            }

        }
    }

    /**
     * @author luffyzhao@vip.126.com
     */
    private function stopIfNecessary()
    {
        if ($this->shouldQuit) {
            $this->stop();
        } elseif ($this->memoryExceeded(128)) {
            $this->stop(12);
        }
    }

    /**
     * @param int $status
     * @author luffyzhao@vip.126.com
     */
    private function stop($status = 0)
    {
        exit($status);
    }

    /**
     * 监听进程信号
     * @author luffyzhao@vip.126.com
     */
    private function listenForSignals()
    {
        pcntl_async_signals(true);

        pcntl_signal(SIGTERM, function () {
            $this->shouldQuit = true;
        });

        pcntl_signal(SIGUSR2, function () {
            $this->paused = true;
        });

        pcntl_signal(SIGCONT, function () {
            $this->paused = false;
        });
    }

    /**
     * @param $memoryLimit
     * @return bool
     * @author luffyzhao@vip.126.com
     */
    private function memoryExceeded($memoryLimit)
    {
        return (memory_get_usage(true) / 1024 / 1024) >= $memoryLimit;
    }

    /**
     * 从队列中取出一条
     * @param $queue
     * @return Job|null
     * @author luffyzhao@vip.126.com
     */
    private function getNextJob($queue): ?Job
    {
        return $this->factory->pop($queue);
    }

    /**
     * 注册等待时间
     * @param $job
     * @author luffyzhao@vip.126.com
     */
    private function registerTimeoutHandler(?Job $job)
    {
        pcntl_signal(SIGALRM, function () {
            $this->kill(1);
        });
        pcntl_alarm(
            max($this->timeoutForJob($job), 30)
        );
    }

    /**
     * 获取job的等待时间
     * @param Job|null $job
     * @return int
     * @author luffyzhao@vip.126.com
     */
    private function timeoutForJob(?Job $job): int
    {
        return $job && $job->getData()->getTimeout() ? $job->getData()->getTimeout() : 0;
    }

    /**
     * 是否支持 pcntl
     * @return bool
     * @author luffyzhao@vip.126.com
     */
    private function supportsAsyncSignals(): bool
    {
        return extension_loaded('pcntl');
    }
}